<?php
use Arboretum\Models\MECEvent;
use Arboretum\Models\MECBooking;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// add_action('edit_form_top', 'add_custom_button');

// function add_custom_button($id){
//     // if ($post->post_type != 'mec-books') return false;
//     echo('<button>Custom button</button>');
// }


// /**
//  * Fires before the Add New button is added to screen.
//  *
//  * The before_add_new- hook fires in a number of contexts.
//  *
//  * The dynamic portion of the hook name, `$pagenow`, is a global variable
//  * referring to the filename of the current page, such as 'edit.php',
//  * 'post.php' etc. A complete hook for the latter would be
//  * 'before_add_new-post.php'.
//  */
// add_action('before_add_new-edit.php');
// if ( current_user_can( $post_type_object->cap->create_posts ) )
//         echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="add-new-h2">TEST</a>';



// /**
//  * Adds the download option in bulk actions
//  */
// function register_generate_spreadsheet_bulk_action($bulk_actions) {
//     $bulk_actions['download_mec-books'] = __('Download Bookings', 'download_tickets');
//     return $bulk_actions;
// }
// add_filter('bulk_actions-edit-mec-books', 'register_generate_spreadsheet_bulk_action');


/**
 * 
 */
function generate_spreadsheet_bulk_action() { //$redirect_url, $action, $post_ids
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



    // if($action == 'download_mec-books') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        
    //     $invalidCharacters = array('*', ':', '/', '\\', '?', '[', ']');
    // $title = str_replace($invalidCharacters, '', $title);
    
        $sheet->setTitle($title);
        // $tickets = get_posts(
        //     array(
        //     'numberposts' => -1,
        //     'include' => $post_ids,
        //     'post_type' => 'ticket'
        //     )
        // );

        // $ticket_num = count($tickets);

        // Set static column titles
        $sheet->setCellValue("A1", "Booking ID");
        $sheet->setCellValue("B1", "Registrant Name");
        $sheet->setCellValue("C1", "Registrant Email");
        $sheet->setCellValue("D1", "Total Registrations");
        $sheet->setCellValue("E1", "Ticket Type");
        $sheet->setCellValue("F1", "Booking Date");
        $sheet->setCellValue("G1", "Event Date");

        // Custom Questions

    //     $sheet->setCellValue("B1", "Ticket Number");
    //     $sheet->setCellValue("C1", "Time Registered");
    //     $sheet->setCellValue("D1", "User Name");
    //     $sheet->setCellValue("E1", "User Email");
    //     $sheet->setCellValue("F1", "City");
    //     $sheet->setCellValue("G1", "State");
    //     $sheet->setCellValue("H1", "Country");
    //     $sheet->setCellValue("I1", "Zip Code");
    //     $sheet->setCellValue("J1", "Event Title");
    //     $sheet->setCellValue("K1", "Start Date");
    //     $sheet->setCellValue("L1", "Selected Venue Location");
    
    //     // Add custom questions
    $custom_question_positions = array();
    $column_number = 6;  // Capital A (65) + 11 other predetermined columns for chr()

    // Get the questions from the event

    //     foreach($tickets as $ticket) {      
    
    //         $get_post_custom = get_post_custom($ticket->ID); 
    //             foreach($get_post_custom as $name=>$value) {
    //                 if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {
    //                     foreach($value as $value_name=>$question) {
    //                         // See if it already contains this answer?
    //                         if (!array_key_exists($question, $custom_question_positions)) {
    //                             $column_letter = chr(65 + ($column_number % 26));
    //                             $column = $column_letter . '1';
    //                             $sheet->setCellValue($column, $question);
    //                             $custom_question_positions[$question] = $column_letter;
                
    //                             $column_number ++;
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //     // Combine custom questions onto the column array
    //     $columns = array_merge(array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'), array_values($custom_question_positions));
    
        $num = 1;
        // Populate rows with submissions
        foreach($bookings as $booking) {

            $book = new MECBooking($booking->ID);
            $attendees = $book->mec_attendees;
            $main_attendee = $attendees[0];
            if (isset($main_attendee['reg'])) {
                $answers = $main_attendee['reg'];
            }
    //         $user = get_user_by('ID', $ticket->user);
            $num ++;
            $sheet->setCellValue("A$num", $book->ID);
            $sheet->setCellValue("B$num", $main_attendee['name']);
            $sheet->setCellValue("C$num", $main_attendee['email']);
            $sheet->setCellValue("D$num", count($attendees));

            // type
            // booking date
            // date
            $book_date = wp_date('Y/m/d - h:i', substr($book->mec_date, 0, strpos($book->mec_date, ':')), new DateTimeZone('America/New_York'));
            $sheet->setCellValue("G$num", $book_date);

            // custom questions
            // if (isset($main_attendee['reg'])) {
            //     foreach($answers as $key=>$value) {
            //         $sheet->setCellValue("F$num", $value);
            //     }
            // }
            // foreach($answers as $key=>$value) {
            //     // if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {
        
            //     //     $question_num = substr($name, 0, strlen($name) - 9);
            //     //     $answer_name = $question_num . '_answer';
            //         $answer = $answers[$key];
            //         foreach($value as $value_name=>$question) {
            //             $column_letter = $custom_question_positions[$question];
            //             $column = $column_letter . $num;
            
            //             $sheet->setCellValue($column, $answer);
            //             }
            //         }
            //     }
            // }
            

    //         $sheet->setCellValue("B$num", $ticket->ID);
    //         $sheet->setCellValue("C$num", $ticket->time_registered);        
    //         $sheet->setCellValue("D$num", "$user->first_name $user->last_name");
    //         $sheet->setCellValue("E$num", $user->user_email);
    //         $sheet->setCellValue("F$num", $user->city);
    //         $sheet->setCellValue("G$num", $user->state);
    //         $sheet->setCellValue("H$num", $user->country);
    //         $sheet->setCellValue("I$num", $user->zip);
    
    //         // Consolidate event data into one string for entry into spreadsheet
    //         $n = 0;
    //         $event_count = count($ticket->event);
    //         $location_count = count($ticket->location);
    //         $titles = '';
    //         $dates = '';
    //         $locations = '';
    
    //         foreach($ticket->event as $event_id) {
    //             $n ++;
    //             $event = new Event($event_id);
    //             $titles .= $event->title;
    //             // TODO: improve date functionality
    //             $dates .= $event->start_date;
    
    //             if($n < $event_count) {
    //                 $titles .= '; ';
    //                 $dates .= '; ';
    //             }
    //         }

    //         $m = 0;
    //         foreach($ticket->location as $location_id) {
    //             $m ++;
    //             $location = new Location($location_id);
    //             $locations .= $location->title;

    //             if($m < $location_count) {
    //                 $locations .= '; ';
    //             }
    //         }
    
    //         $sheet->setCellValue("J$num", $titles);
    //         $sheet->setCellValue("K$num", $dates);
    //         $sheet->setCellValue("L$num", $locations);
    
            
    //         $get_post_custom = get_post_custom($ticket->ID); 
    //         foreach($get_post_custom as $name=>$value) {
    //         if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {
    
    //             $question_num = substr($name, 0, strlen($name) - 9);
    //             $answer_name = $question_num . '_answer';
    //             $answer = $get_post_custom[$answer_name][0];
    //             foreach($value as $value_name=>$question) {
    //                 $column_letter = $custom_question_positions[$question];
    //                 $column = $column_letter . $num;
        
    //                 $sheet->setCellValue($column, $answer);
    //                 }
    //             }
    //         }
    //     }

    //     // Set column width and text-wrap
    //     foreach($columns as $column) {
    //         $sheet->getColumnDimension($column)->setAutoSize(true);
    //         // $sheet->getColumnDimension($column)->setWidth('50');
            
    //         $sheet->getStyle($column . '1:' . $column . '1')->getFont()->setBold(true);
    //         $sheet->getStyle($column . '1:' . $column . '1')->getAlignment()->setHorizontal('center');
    //         $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setWrapText(true); 
    //         $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setHorizontal('left'); 
    //         // $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setIndent(1); 
        }

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
    // }

    // return $redirect_url;
}
// add_filter('handle_bulk_actions-edit-mec-books', 'generate_spreadsheet_bulk_action', 10, 3);
  
// add_action( 'restrict_manage_posts', 'customized_filters' );
function add_download_data_button($which)
{
//     if ($post_type != 'mec-books') return;
// ? >
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
        $event_id = $_GET['mec_event_id'];

        $args = array(
            'numberposts'   => -1,
            'post_type'     => 'mec-books',
            'meta_key'      => 'mec_event_id',
            'meta_value'    => $event_id
        );
    
        // Get Event
        // $mecEvent = new MECEvent($event_id);

        $bookings = get_posts($args);
        $mecEvent = new MECEvent($event_id);

        var_dump($bookings[0]);
        echo '<hr>';

        foreach($bookings as $book) {
            $booking = new MECBooking($book->ID);
            var_dump($booking);
            echo '<hr>';
        }
        var_dump($mecEvent);
    }

    // `<input id="post-query-export" class="button" type="button" value="Export CSV list" name="" onclick="document.location.href=` . get_stylesheet_directory_uri() . `/csv/export.php';">`;
// <?php  
}
add_action('manage_posts_extra_tablenav', 'add_download_data_button');


function export_data()
{
    global $typenow;
    if ('mec-books' !== $typenow) return;

    if (isset($_GET['export_data'])) {
        generate_spreadsheet_bulk_action();
    }
}
add_action('wp', 'export_data');