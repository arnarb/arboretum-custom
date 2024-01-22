<?php
require_once('/www/arnoldarboretumwebsite_753/public/wp-load.php');
/**
 * Pull the powerdash data from their API
 */
function store_images() {
    // $filename = $_SERVER['DOCUMENT_ROOT'] . 'public/algolia_calc.json';
    // $file = file_get_contents($filename);
    // $json = json_decode($file, true);

    
    if (file_exists("/www/arnoldarboretumwebsite_753/public/wp-load.php") ){
        if (is_readable("/www/arnoldarboretumwebsite_753/public/wp-load.php")) {
            if (include("/www/arnoldarboretumwebsite_753/public/wp-load.php")) { 
                $headers = array(
                    "Content-Type: text/html; charset=UTF-8\r\n",
                    'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
                );
                $to                 = 'matthew_caulkins@harvard.edu';
                $subject            = 'Arnoldia cron';
                $body               = 'starting cron';
                wp_mail($to, $subject, $body, $headers);
            }
        }
    }


    // $new_json = [];
    // $attempt = false;

    // foreach($json as $article) {
    //     if (!$article['local_photo']) {
    //         $img = $_SERVER['DOCUMENT_ROOT'] . 'public/wp-content/uploads/arnoldia/' . substr($article['featured_photo'], strpos($article['featured_photo'], '?file='));
    //         $url = $article['featured_photo'];

    //         file_put_contents($img, file_get_contents($url));            
    //         $article['local_photo'] = $url;
    //     }

    //     if (!$attempt) {
    //         $to                 = 'matthew_caulkins@harvard.edu';
    //         $subject            = 'Arnoldia cron';
    //         $body               = json_encode($article, JSON_PRETTY_PRINT);
    //         wp_mail($to, $subject, $body, $headers);
    //         $attempt = true;
    //     }
    //     $new_json = (object) array_merge((array) $new_json, $article);
    // }

    // $to                 = 'matthew_caulkins@harvard.edu';
    // $subject            = 'Arnoldia cron';
    // $body               = 'ending cron';
    // wp_mail($to, $subject, $body, $headers);

    // $data = json_encode($new_json, JSON_PRETTY_PRINT);
    // $fp = fopen($filename, 'w');
    // fwrite($fp, $data);
    // fclose($fp);
    // download the 
}

store_images();
?>