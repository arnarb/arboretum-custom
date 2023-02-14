<?php
require_once('/www/arnoldarboretumwebsite_753/public/wp-load.php');

use Arboretum\Repositories\EventRepository;
use Arboretum\Repositories\TicketRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;

/**
 * Send reminder emails to event registrants
 */
function send_event_reminders() {
    $settings = get_fields('options');

    date_default_timezone_set('America/New_York');
    $current_date = date("Y-m-d H:i:s");

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>'
    );

    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    $body = 'running<br>';
    if (file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if (is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            if (include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                $tickets = $ticketRepo->getTickets(-1)->get();

                foreach($tickets as $ticket) {
                    if ($ticket->on_waitlist == 0) {
                        if (!isset($ticket->reminder_email_sent) || empty($ticket->reminder_email_sent)) {
                            $event_date = $ticket->event_date;
                            $reminder_time = isset($event->hours_prior) ? $event->hours_prior : $settings['reminder_email']['hours_prior'];

                            if (strtotime($current_date) + ($reminder_time * 3600) > (strtotime($event_date))) {
                                $event_id = $ticket->event[0];
                                $event = new Event($event_id);
                                $location = new Location($ticket->location[0]);

                                // LINK THE OTHER TICKETS IN THIS GROUP
                                    // ITERATE OVER THEM TO CANCEL

                                $query = ''; //'?tickets=' . count($tickets);
                                // for ($n = 1; $n <= count($tickets); $n++) {
                                //   $query .= '&id_' . $n . '=' . $tickets[$n-1] . '&q_' . $n . '=' . $hashs[$n-1];
                                // }
                                $cancel_link        = 'https://staging-arnoldarboretumwebsite.kinsta.cloud/events/cancel-event-registration/' . $query;
                                
                                $venues = get_field('venues', $event_id);
                                foreach($venues as $venue) {
                                    if ($venue['location'] = $location) { // Only calculate for the proper location
                                        $directions = !empty($venue['directions']) ? $venue['directions'] : $location->directions;
                                    }
                                }
                                    
                                $to                 = $ticket->email;
                                $subject            = 'Reminder for ' . $event->title . ' at the Arnold Arboretum';
                                $body               = isset($event->reminder_email) && isset($event->reminder_email['body']) ? $event->reminder_email['body'] : $settings['reminder_email']['body'];
                                $tags               = array('[event]', '[date]', '[venue]', '[cancelation_link]', '[directions]'); 
                                $date               = date("F jS", strtotime($event_date));
                                $values             = array($event->title, $date, $location->post_title, $cancel_link, $directions);
                                $body               = str_replace($tags, $values, $body);
                                
                                wp_mail($to, $subject, $body, $headers);
                                
                                update_post_meta($ticket->ID, 'reminder_email_sent', $current_date);
                            }
                        }
                    }
                }
            }
        }
    }

    date_default_timezone_set('UTC');
}

/**
 * Send follow-up survey emails to event registrants
 */
function send_event_surveys() {
    $settings = get_fields('options');

    date_default_timezone_set('America/New_York');
    $current_date = date("Y-m-d H:i:s");

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>'
    );

    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    if (file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if (is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            if (include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                $tickets = $ticketRepo->getTickets(-1)->get();

                foreach($tickets as $ticket) {
                    if ($ticket->on_waitlist == 0) {
                        if (!isset($ticket->follow_up_survey_sent) || empty($ticket->follow_up_survey_sent)) {
                            $event_date = $ticket->event_date;
                            $reminder_time = $settings['follow_up_survey']['hours_after'];

                            // if (strtotime($current_date) - ($reminder_time * 3600) > (strtotime($event_date))) {
                                $event_id = $ticket->event[0];
                                $event = new Event($event_id);
                                $survey = $event->survey_url;

                                if (!isset($survey) || empty($survey)) {
                                    if ($ticket->survey_missing_reminder_sent == 0) {
                                        $to                 = PUBLIC_PROGRAMS_EMAIL;
                                        $subject            = 'Survey missing for ' . $event->title;
                                        $url                = 'https://staging-arnoldarboretumwebsite.kinsta.cloud/wp-admin/post.php?post=' . $event_id; // set to live site url
                                        $body               = 'Please add a Surveymonkey link for <a href="' . $url . '&action=edit&lang=en">' . $event->title . '</a>.';

                                        wp_mail($to, $subject, $body, $headers);

                                        update_post_meta($ticket->ID, 'survey_missing_reminder_sent', 1);
                                    }
                                } else {
                                    $to                 = $ticket->email;
                                    $subject            = 'Survey for ' . $event->title . ' at the Arnold Arboretum';
                                    $body               = isset($event->follow_up_survey) && isset($event->follow_up_survey['body']) ? $event->follow_up_survey['body'] : $settings['follow_up_survey']['body'];
                                    $tags               = array('[event]', '[survey]'); 
                                    $date               = date("F jS", strtotime($event_date));
                                    $values             = array($event->title, $survey);
                                    $body               = str_replace($tags, $values, $body);

                                    $body .= 'Survey link : ' . $survey;
                                    
                                    wp_mail($to, $subject, $body, $headers);
                                    
                                    update_post_meta($ticket->ID, 'follow_up_survey_sent', $current_date);
                                }
                            // }
                        }
                    }
                }
            }
        }
    }

    date_default_timezone_set('UTC');
}

/**
 * send an email to me
 */
// function send_email() {
    
//     $to                 = 'matthew_caulkins@harvard.edu';
//     $subject            = 'test cronjob';
//     $body               = 'STAGING Woohoo!';
    
//     wp_mail($to, $subject, $body);
// }
// send_email();
send_event_reminders();
send_event_surveys();