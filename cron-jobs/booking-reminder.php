<?php
/**
 *  WordPress initializing
 */ 
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require dirname(__FILE__).'/wp-load.php';

use Arboretum\Repositories\MECEventRepository;
use Arboretum\Models\MECBooking as MECBooking;
use Arboretum\Models\MECEvent as MECEvent;

// Get the time discrepency
// date_default_timezone_set('America/New_York');
$current_time = date('m/d/Y h:i:s a', time());
$one_day = 86400;

// MEC libraries
$main = MEC::getInstance('app.libraries.main');
$notif = $main->getNotifications();

$no_reminder_bookings = [];
$reminder_bookings = [];

$mecRepo = new MECEventRepository();
$mecEvents = $mecRepo->getUpcomingEvents()->get();

foreach($mecEvents as $event):
    $bookings = $main->get_bookings($event->ID);

    foreach($bookings as $booking):
        $book = new MECBooking($booking->ID);

        // Check the time on the ticket - is it tomorrow
        if (empty($book->reminder_sent)) {
            // If they registered in less than one day from the current time set it to already sent
            if ($book->mec_attention_time_start < (strtotime($current_time) + $one_day)) {
                if (($book->mec_attention_time_start < strtotime($current_time)) || (strtotime($book->mec_booking_time) + $one_day) > $book->mec_attention_time_start) {
                    update_field('reminder_sent', 1, $book->ID);
    
                    $no_reminder_bookings[$book->ID] = $book;
                } else {
                    $notif->booking_reminder($book->ID);
                    update_field('reminder_sent', 1, $book->ID);

                    $reminder_bookings[$book->ID] = $book;
                }
            }
        }
    endforeach;
endforeach;

// echo "Booking Reminders:\n\n";
if (count($reminder_bookings) > 0) {
    echo "Sent a reminder to " . count($reminder_bookings) . " booking(s).\n";

    foreach($reminder_bookings as $booking):
        // echo "Current Time: " . strtotime($current_time);
        // echo "\n\nMEC Start Time: " . $book->mec_attention_time_start;
        // echo "\n\nBooking Time: " . strtotime($book->mec_booking_time);


        $event = new MECEvent($booking->mec_event_id);
        echo "\n_____________________\n\n";
        echo "Ticket Title: " . $booking->post_title . "\r\n";
        echo "Transaction ID: " . $booking->mec_transaction_id . "\r\n"; 
        echo "Event Title: " . $event->post_title . "\r\n";
        echo 'Start Time: ' . date('Y-m-d h:i A', $booking->mec_attention_time_start) . '- End Time: ' . date('Y-m-d h:i A', $booking->mec_attention_time_end) . "\r\n";
        $email = "https://staging-arnoldarboretumwebsite.kinsta.cloud/wp-admin/post.php?post=" . $booking->ID . "&action=edit";
        echo $email;

        echo "\n\nAttendees:\n";
        foreach($booking->mec_attendees as $attendee):
            echo "   Attendee:\n";
            echo "     " . $attendee['name'];
            echo "\n     " . $attendee['email'];
        endforeach;
        echo "\r\n"; 
    endforeach;
}

if (count($no_reminder_bookings) > 0) {
    echo "\n\nDid not send a reminder to " . count($no_reminder_bookings) . " booking(s).  They were registered within 24 hours of the event.\n";
    foreach($no_reminder_bookings as $booking):
        // echo "Current Time: " . strtotime($current_time);
        // echo "\n\nMEC Start Time: " . $book->mec_attention_time_start;
        // echo "\n\nBooking Time: " . strtotime($book->mec_booking_time);


        $event = new MECEvent($booking->mec_event_id);
        echo "\n_____________________\n\n";
        echo "Ticket Title: " . $booking->post_title . "\r\n";
        echo "Transaction ID: " . $booking->mec_transaction_id . "\r\n"; 
        echo "Event Title: " . $event->post_title . "\r\n";
        echo 'Start Time: ' . date('Y-m-d h:i A', $booking->mec_attention_time_start) . '- End Time: ' . date('Y-m-d h:i A', $booking->mec_attention_time_end) . "\r\n";
        $email = "https://staging-arnoldarboretumwebsite.kinsta.cloud/wp-admin/post.php?post=" . $booking->ID . "&action=edit";
        echo $email;

        echo "\n\nAttendees:\n";
        foreach($booking->mec_attendees as $attendee):
            echo "   Attendee:\n";
            echo "     " . $attendee['name'];
            echo "\n     " . $attendee['email'];
        endforeach;
        echo "\r\n"; 
    endforeach;
}

// date_default_timezone_set('UTC');
exit;