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

$survey_bookings = [];

$mecRepo = new MECEventRepository();
$mecEvents = $mecRepo->getUpcomingEvents()->get();

$eventsSent = [];

foreach($mecEvents as $event):
    $bookings = $main->get_bookings($event->ID);

    foreach($bookings as $booking):
        $book = new MECBooking($booking->ID);

        // Check the time on the ticket - is it tomorrow
        if (empty($book->survey_sent)) {
            if ($book->mec_attention_time_end < (strtotime($current_time) - $one_day)) {
                update_field('survey_sent', 1, $book->ID);
                $survey_bookings[$book->ID] = $book;

                if (!in_array($booking->mec_event_id, $eventsSent)) {
                    array_push($eventsSent, $booking->mec_event_id);
                    $notif->event_finished($booking->mec_event_id, $book->mec_attention_time_start.':'.$book->mec_attention_time_end);
                }
            }
        }
    endforeach;
endforeach;

// echo "Follow-up Surveys:\n\n";
if (count($survey_bookings) > 0) {
    echo "Sent a survey to " . count($survey_bookings) . " booking(s).\r\n";

    foreach($survey_bookings as $booking):
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