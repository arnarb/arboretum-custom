<?php
use Arboretum\Models\MECEvent;
use Arboretum\Models\MECWaiting;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

function generate_waitlist_spreadsheet() { 
    $date = date("Y-m-d");
    $meta_query = array();

    if (isset($_GET['mec_event_id'])) {
        $event_id = $_GET['mec_event_id'];
    } else {
        return;
    }

    $args = array(
        'numberposts'   => -1,
        'post_type'     => 'mec-waiting',
        'meta_key'      => 'mec_event_id',
        'meta_value'    => $event_id
    );

    // Get Event
    $mecEvent = new MECEvent($event_id);
    $waitings = get_posts($args);
    $title = 'Waitlist Registrations';
    $long_title = 'Waitlist Registrations - ' . $mecEvent->post_title . ' - ' . $date;

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

    $max_column_number = chr(65 + ($column_number % 26));
    
    $num = 1;
    // Populate rows with submissions
    foreach($waitings as $waiting) {
        $waiting = new MECWaiting($waiting->ID);
        $attendees = $waiting->mec_attendees;
        $main_attendee = $attendees[0];
        if (isset($main_attendee['reg'])) {
            $answers = $main_attendee['reg'];
        }

        $num ++;
        // waiting id
        $sheet->setCellValue("A$num", $waiting->ID);

        // registrants info
        $sheet->setCellValue("B$num", $main_attendee['name']);
        $sheet->setCellValue("C$num", $main_attendee['email']);

        // verification, waiting, canceled
        $values = array('-1' => 'Canceled', '0' => 'Waiting', '1' => 'Verified');
        $value = $values[$waiting->mec_verified];
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
        if (gettype($waiting->mec_ticket_id) != "array")
        {
            $types = explode(',', $waiting->mec_ticket_id);
        } else {
            $types = $waiting->mec_ticket_id;
        }
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
        $book_date = get_the_date('j F, Y @ g:i A', $waiting);
        $sheet->setCellValue("G$num", $book_date);

        // date
        $timestamp = substr($waiting->mec_date, 0, strpos($waiting->mec_date, ':'));
        $event_date = date('j F, Y @ g:i A', $timestamp);
        $sheet->setCellValue("H$num", $event_date);
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
function add_waiting_export_data_button($which)
{
    global $typenow;
    if ('mec-waiting' !== $typenow) return;
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
add_action('manage_posts_extra_tablenav', 'add_waiting_export_data_button');

// Export data for selected event
function export_waiting_data()
{
    global $typenow;
    if ('mec-waiting' !== $typenow) return;

    if (isset($_GET['export_data'])) {
        generate_waitlist_spreadsheet();
    }
}
add_action('wp', 'export_waiting_data');