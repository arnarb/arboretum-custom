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
    $current_date = date("Y-m-d");

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
    );

    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    $body = 'running<br>';
    if( file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if( is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            if( include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 

                $tickets = $ticketRepo->getTickets(-1)->get();

                foreach($tickets as $ticket) {

                    if (!isset($ticket->reminder_email_sent) || empty($ticket->reminder_email_sent)) {
                        $event_date = $ticket->event_date;
                        $reminder_time = isset($event->hours_prior) ? $event->hours_prior : $settings['reminder_email']['hours_prior'];

                        if (strtotime($current_date) + ($reminder_time * 3600) > (strtotime($event_date))) {
                        // Check that it is 24 hours or the event time away or less {

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
                            //     $capacity = $venue['capacity'];
                            //     $location_id = intval($venue['location'][0]->ID);
                            //     $location = new Location($location_id); 

                                if ($venue['location'] = $location) { // Only calculate for the proper location
                                    $directions = !empty($venue['directions']) ? $venue['directions'] : $location->directions;
                                }
                            }
                                
                            $to                 = $ticket->email;
                            $subject            = 'Reminder for ' . $event->title . ' at the Arnold Arboretum';
                            $body               = isset($event->reminder_email) && isset($event->reminder_email['body']) ? $event->reminder_email['body'] : $settings['reminder_email']['body'];
                            $tags               = array('[event]', '[date]', '[venue]', '[cancelation_link]', '[directions]'); // array('[event]', '[date]', '[time]', '[venue]', '[cancelation_link]', '[directions]', '[map]');
                            $date               = date("F jS", strtotime($event_date));
                            // $time               = date("g:ma",strtotime($event_date)) . ' - ' . $end_time;
                            $values             = array($event->title, $date, $location->post_title, $cancel_link, $directions); // array($event->title, $date, $time, $location->post_title, $cancel_link, $directions, $map_link);
                            $body               = str_replace($tags, $values, $body);
                            
                            wp_mail($to, $subject, $body, $headers);
                            
                            update_field('reminder_email_sent', $current_date, $ticket->ID);
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
    $date = date("Y-m-d");

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
    );

    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    if( file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if( is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            if( include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                
                $tickets = $ticketRepo->getTickets(-1)->get();

                foreach($tickets as $ticket) {

                    if (!isset($ticket->reminder_email_sent)) {
                        $event_date = $ticket->event_date;
                        $reminder_time = isset($event->hours_prior) ? $event->hours_prior : $settings['reminder_email']['hours_prior'];

                    }
                    
                    // $eventsToday = $eventRepo->getEvents(-1)->get();
                    // $body .= "<br><br>Events needing follow-up emails sent:<br><br>";

                    // foreach($eventsToday as $event) {
                    //     $body .= $event->title . '<br>';
                    // }

                    $to                 = 'matthew_caulkins@harvard.edu';
                    $subject            = 'Follow-up survey';
                    $body               = $body;
                    
                    wp_mail($to, $subject, $body, $headers);
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
// send_event_surveys();