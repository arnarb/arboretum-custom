<?php
require_once('/www/arnoldarboretumwebsite_753/public/wp-load.php');

use Arboretum\Repositories\EventRepository;
use Arboretum\Repositories\TicketRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;
/**
 * Find events today and remind the registrants
 */
function find_events_today() {
    date_default_timezone_set('America/New_York');
    $date = date("Y-m-d");
    $eventRepo = new EventRepository();
    $ticketRepo = new TicketRepository();

    
    
    $body = 'running';
    if( file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if( is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            $body .= "    wp-load.php is readable";
            if( include("/www/arnoldarboretumwebsite_753/public/wp-load.php")){ 
                $body .= "    wp-load.php is included."; 


                $body .= get_bloginfo('name');

                // $body .= var_dump($eventRepo);

                $params = [
                    'post_type'      => 'event',
                    'orderby'        => 'meta_value',
                    'order'          => 'ASC',
                    'posts_per_page' => -1,
                ];

                $posts = new WP_Query($params);

                $body .= $posts;

                // $eventsToday = $eventRepo.getEvents()->get();
                // $body = '    Events Today:  ';

                // foreach($eventsToday as $event) {
                //     $body .= $event->title . ', ';
                // }
            }
        }
    }
    $to                 = 'matt.caulkins@gmail.com';
    $subject            = 'test cronjob';
    $body               = $body;
    
    wp_mail($to, $subject, $body);

    date_default_timezone_set('UTC');
}


/**
 * send an email to me
 */
function send_email() {
    
    $to                 = 'matt.caulkins@gmail.com';
    $subject            = 'test cronjob';
    $body               = 'STAGING Woohoo!';
    
    wp_mail($to, $subject, $body);
}
// send_email();
find_events_today();