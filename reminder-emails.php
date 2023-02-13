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
    $date = date("Y-m-d");

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
    );

    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    $body = 'running<br>';
    if( file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if( is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            $body .= "wp-load.php is readable<br>";
            if( include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                $body .= "wp-load.php is included.<br>"; 


                $body .= get_bloginfo('name');

                // $body .= var_dump($eventRepo);

                // $params = [
                //     'post_type'      => 'event',
                //     'orderby'        => 'meta_value',
                //     'order'          => 'ASC',
                //     'posts_per_page' => -1,
                // ];

                // $posts = new WP_Query($params);

                // $body .= $posts;

                $tickets = $ticketRepo->getTickets(-1)->get();
                $body .= "<br><br>Tickets needing reminder emails sent:<br><br>";

                foreach($tickets as $ticket) {
                    $event = new Event($ticket->event[0]);
                    $body .= $ticket->title . '  -  ' . $event->title . '<br>';
                }
            }
        }
    }
    $to                 = 'matthew_caulkins@harvard.edu';
    $subject            = 'Reminder email';
    $body               = $body;
    
    wp_mail($to, $subject, $body, $headers);

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

    $body = 'running<br>';
    if( file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if( is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            $body .= "wp-load.php is readable<br>";
            if( include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                $body .= "wp-load.php is included.<br>"; 


                $body .= get_bloginfo('name');

                // $body .= var_dump($eventRepo);

                // $params = [
                //     'post_type'      => 'event',
                //     'orderby'        => 'meta_value',
                //     'order'          => 'ASC',
                //     'posts_per_page' => -1,
                // ];

                // $posts = new WP_Query($params);

                // $body .= $posts;

                $eventsToday = $eventRepo->getEvents(-1)->get();
                $body .= "<br><br>Events needing follow-up emails sent:<br><br>";

                foreach($eventsToday as $event) {
                    $body .= $event->title . '<br>';
                }
            }
        }
    }
    $to                 = 'matthew_caulkins@harvard.edu';
    $subject            = 'Follow-up survey';
    $body               = $body;
    
    wp_mail($to, $subject, $body, $headers);

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