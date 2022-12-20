<?php
include '/public/wp-load.php';

/**
 * send an email to me
 */
function send_email() {
    
    $to                 = 'matt.caulkins@gmail.com';
    $subject            = 'test cronjob';
    $body               = 'Woohoo!';
    
    wp_mail($to, $subject, $body);
}
send_email();


function send_normal_email() {
    $to                 = 'matt.caulkins@gmail.com';
    $subject            = 'test cronjob php without wp';
    $body               = 'Test of normal message from PHP';

    mail($to, $subject, $body);)
}
send_normal_email();