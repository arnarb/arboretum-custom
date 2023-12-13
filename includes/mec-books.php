<?php
use Arboretum\Models\MECEvent;
use Arboretum\Models\MECBooking;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

function generate_spreadsheet_bulk_action() { 
    $date = date("Y-m-d");
    $meta_query = array();

    if (isset($_GET['mec_event_id'])) {
        $event_id = $_GET['mec_event_id'];
    } else {
        return;
    }

    $args = array(
        'numberposts'   => -1,
        'post_type'     => 'mec-books',
        'meta_key'      => 'mec_event_id',
        'meta_value'    => $event_id
    );

    // Get Event
    $mecEvent = new MECEvent($event_id);
    $bookings = get_posts($args);
    $title = 'Event Registrations';
    $long_title = 'Event Registrations - ' . $mecEvent->post_title . ' - ' . $date;

    $ticket_types = array();
    $types = $mecEvent->mec_tickets;

    foreach ($types as $key=>$type) {
        if (!in_array($key, $ticket_types)) {
            $ticket_types[$key] = $type['name'];
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($title);
    $sheet->setCellValue("A1", "Booking ID");
    $sheet->setCellValue("B1", "Registrant Name");
    $sheet->setCellValue("C1", "Registrant Email");
    $sheet->setCellValue("D1", "Verification");
    $sheet->setCellValue("E1", "Total Registrations");
    $sheet->setCellValue("F1", "Ticket Type");
    $sheet->setCellValue("G1", "Booking Date");
    $sheet->setCellValue("H1", "Event Date");

    // Add custom questions
    $custom_question_positions = array();
    $column_number = 8;  // Capital A (65) + 7 other predetermined columns for chr()

    $ignore_values = array('name', 'mec_email', 'p');

    $columns = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

    // Custom Questions
    if ($mecEvent->mec_reg_fields_global_inheritance == '0') {
        foreach ($mecEvent->mec_reg_fields as $key=>$question) {
                // See if it already contains this answer?
            if (!array_key_exists($key, $custom_question_positions)) {
                $column_letter = chr(65 + ($column_number % 26));
                $cell = $column_letter . '1';

                if (isset($question['label']) && !in_array($question['type'], $ignore_values)) {
                    $sheet->setCellValue($cell, $question['label']);
                    $custom_question_positions[$key] = $column_letter;

                    $column_number++;

                    array_push($columns, $column_letter);
                }
            }
        }
    } else {
        $sheet->setCellValue('I1', 'THIS EVENT INHERITS FROM THE GLOBALS');
        array_push($columns, 'I');
    }

    $max_column_number = chr(65 + ($column_number % 26));
    
    $num = 1;
    // Populate rows with submissions
    foreach($bookings as $booking) {
        $book = new MECBooking($booking->ID);
        $attendees = $book->mec_attendees;
        $main_attendee = $attendees[0];
        if (isset($main_attendee['reg'])) {
            $answers = $main_attendee['reg'];
        }

        $num ++;
        // booking id
        $sheet->setCellValue("A$num", $book->ID);

        // registrants info
        $sheet->setCellValue("B$num", $main_attendee['name']);
        $sheet->setCellValue("C$num", $main_attendee['email']);

        // verification, waiting, canceled
        $values = array('-1' => 'Canceled', '0' => 'Waiting', '1' => 'Verified');
        $value = $values[$book->mec_verified];
        $richText = new RichText();
        $verified = $richText->createTextRun($value);
        $verified->getFont()->setBold(true);
        switch($value) {
            case 'Canceled':
                $verified->getFont()->setColor(new Color('FFFF4400'));
                break;
            case 'Verified':
                $verified->getFont()->setColor(new Color('FF00B400'));
                break;
        }
        $sheet->getCell("D$num")->setValue($richText);

        // attendees
        $sheet->setCellValue("E$num", count($attendees));

        // type
        $types = explode(',', $book->mec_ticket_id);
        $tickets_purchased = array();

        foreach ($types as $type) {
            if ($type == "") continue;

            $ticket_type = $ticket_types[$type];
            if (isset($tickets_purchased[$ticket_type])) {
                $tickets_purchased[$ticket_type] ++;
            } else {
                $tickets_purchased[$ticket_type] = 1;
            }
        }
        $tickets = '';
        $total = count($tickets_purchased);
        $counter = $total;

        foreach($tickets_purchased as $key => $number) {
            if ($total > 1) {
                $tickets .= $key . ' (' . $number . ')';

                if ($counter > 1) {
                    $tickets .= "\n";
                }

                $counter --;
            } else {
                $tickets = $key;
            }
        }
        $sheet->setCellValue("F$num", $tickets);

        // booking date
        $book_date = date('j F, Y @ g:i A', strtotime($book->mec_booking_time));
        $sheet->setCellValue("G$num", $book_date);

        // date
        $timestamp = substr($book->mec_date, 0, strpos($book->mec_date, ':'));
        $event_date = date('j F, Y @ g:i A', $timestamp);
        $sheet->setCellValue("H$num", $event_date);

        foreach($answers as $key => $answer) {
            if (array_key_exists($key, $custom_question_positions)) {
                $column_letter = $custom_question_positions[$key];
                $cell = $column_letter . $num;
    
                if (is_array($answer)) {
                    $answer = implode(', ', $answer);
                }
                $sheet->setCellValue($cell, $answer);
            }
        }
    }

    // Set auto width and text wrap
    foreach ($columns as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    $sheet->getStyle("A2:$max_column_number$num")->getAlignment()->setWrapText(true);

    // Write excel sheet to file
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"$long_title.xlsx\"");
    header("Cache-Control: max-age=0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");
    $writer->save("php://output");
    die;
}

// Add the export data button
function add_export_data_button($which)
{
    global $typenow;
    if ('mec-books' !== $typenow) return;
    if ($which == 'top') return;

    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]&export_data=true";
    $onclick = "location.href='$url'";

    if (!isset($_GET['mec_event_id'])) {
        echo '<hr><h3>Export Data button will only be active when filtered for a single event</h3>';
        echo '<input type="button" value="Export Data" class="button" disabled/>';
    } else {
        echo '<hr><input type="button" value="Export Data" onclick="' . $onclick . '" class="button"/>';
    }
}
add_action('manage_posts_extra_tablenav', 'add_export_data_button');

// Export data for selected event
function export_data()
{
    global $typenow;
    if ('mec-books' !== $typenow) return;

    if (isset($_GET['export_data'])) {
        generate_spreadsheet_bulk_action();
    }
}
add_action('wp', 'export_data');