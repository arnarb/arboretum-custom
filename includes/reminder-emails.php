<?php
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