<?php
/**
 * Filter for where posts using sub-fields of repeaters
 */
function repeater_nested_where( $where ) {
	
	$where = str_replace("meta_key = 'venues_$", "meta_key LIKE 'venues_%", $where);

	return $where;
}

add_filter('posts_where', 'repeater_nested_where');


/**
 * Sort Art Shows and Events for the event page listings
 */
function sort_shows_and_events($events) {
    $format = 'Y-m-d H:i:s';
    $posts = array();

    foreach($events as $event) {
        if ($event->custom['venues']) { // Events
            $start_date = null;
            $end_date = null;
            $event_dates = array();
            $start_found = false;

            for ($i = 0; $i < $event->custom['venues']; $i++) {
                $start_label = 'venues_' . $i . '_start_date';
                $end_label = 'venues_' . $i . '_end_date';
                $dates_label = 'venues_' . $i . '_event_dates';

                if($event->$dates_label) {
                    for($j = 0; $j < $event->$dates_label; $j++) {
                        $date_label = 'venues_' . $i . '_event_dates_' . $j . '_date';
                        array_push($event_dates, date($format, strtotime($event->$date_label)));
                    }
                } else {
                    if ($start_date === null || $start_date > $event->custom[$start_label]) {
                        // Sort out the start dates?
                        $start_date = $event->$start_label;
                    }

                    if ($event->$end_label) {
                        if ($end_date === null || $end_date < $event->custom[$end_label]) {
                            // Sort out the end dates?
                            $end_date = $event->$end_label;
                        }
                    }
                }
            }

            if(count($event_dates) > 0) {
                usort($event_dates, function($a, $b) {
                    if ($a == $b):
                        return (0);
                    endif;

                    return (($a > $b) ? 1 : -1);
                });

                for($k = 0; $k < count($event_dates); $k++) {
                    if(!$start_found) {
                        // Assumes the next available day so we don't continue counting in the past for nonsequential dates
                        if($event_dates[$k] > date($format)) {
                            $event->start_date = date($format, strtotime($event_dates[$k]));
                            $start_found = true;
                        }
                    }
                }

                if (count($event_dates) > 1) {
                    $event->end_date = date($format, strtotime($event_dates[count($event_dates) - 1]));
                }
                // $event->start_date = date($format, strtotime($start_date));
                // $event->end_date = date($format, strtotime($end_date));
                // $event->start_date2 = date($format, strtotime($event_dates[0]));
            } else {
                $event->start_date = date($format, strtotime($start_date));
                $event->end_date = date($format, strtotime($end_date));
            }
        } else {  // Art Shows
            if ($event->start_date) {
                $event->start_date = date($format, strtotime($event->start_date)); // substr($event->start_date, 0, 4) . '-' . substr($event->start_date, 4, 2) . '-' . substr($event->start_date, 6) . ' 00:00:00'; // 23:59:59
            }
            if ($event->end_date) { //} && !strpos($event->end_date, '-')) {
                $event->end_date = date($format, strtotime($event->end_date)); // substr($event->end_date, 0, 4) . '-' . substr($event->end_date, 4, 2) . '-' . substr($event->end_date, 6) . ' 00:00:00'; // 23:59:59
            }
        }

        // Don't add multi-day events that have already passed
        if($event->start_date) {
            array_push($posts, $event);
        }
    }

    usort($posts, function($a, $b)
    {
        if ($a->start_date == $b->start_date):
            return (0);
        endif;

        return (($a->start_date > $b->start_date) ? 1 : -1);
    });

    return $posts;
}


/**
 * Special for group of items to be blocked into eras
 */
function sort_items($eras, $sort_value) {
    foreach($eras as $era => $items) {
        usort($items, function($a, $b) use ($sort_value) {
            if ($a->custom[$sort_value] == $b->custom[$sort_value]):

                if (isset($a->custom['order']) && isset($b->custom['order'])):
                    return (($a->custom['order'] > $b->custom['order']) ? -1 : 1);
                else:
                    return (0);
                endif;
            endif;

            return (($a->custom[$sort_value] > $b->custom[$sort_value]) ? -1 : 1);
        });
        $eras[$era] = $items;
    }
    return $eras;
}


/**
 * Get an array of arrays sorted into 3 eras, 1874-1927, 1928-1976, 1977-present
 */
function get_items_by_era($items, $sort_value = "start_year") {
    $items_by_era = array();

    foreach($items as $item) {
        $era = $item->custom[$sort_value] > 1976 ? "1977-present" : 
            ($item->custom[$sort_value] > 1927 ? "1928-1976" : "1874-1927");

        if (array_key_exists($era, $items_by_era)) {
            array_push($items_by_era[$era], $item);
        } else {
            $items_by_era[$era] = [$item];
        }
    }

    return sort_items($items_by_era, $sort_value);
}


/**
 * Get an array of arrays sorted into individual years
 *
 * @param Array items         A list of objects to be sorted by year
 * @param string sort_value   A string to sort the list by, assumes YYYY-mm-dd hh:mm:ss date-time format
 */
function get_items_by_year($items, $sort_value = "") {
    $items_by_year = array();

    foreach($items as $item) {
        $year = substr($item->custom[$sort_value], 0, 4);

        if (array_key_exists($year, $items_by_year)) {
            array_push($items_by_year[$year], $item);
        } else {
            $items_by_year[$year] = [$item];
        }
    }

    return sort_items($items_by_year, $sort_value);
}


/**
 * Helper function to convert an Array of Arrays to an Array of Objects
 * A field returns an array of arrays, but a repository returns an array of objects
 *
 * @param Array of arrays
 * @return Array of objects
 * /
*function to_array_of_objects($array_of_arrays) {
 * $array_of_objs = [];
*
*  foreach($array_of_arrays as $array):
*      $obj = (object) $array;
*      array_push($array_of_objs, $obj);
*  endforeach;
*
*  return $array_of_objs;
*}
*/



/**
 * Helper function to convert an Array of Arrays to an Array of Objects
 * A field returns an array of arrays, but a repository returns an array of objects
 *
 * @param Array of arrays
 * @return Array of objects
 */
function convert_to_array_of_objects($items) {
    $array_of_objs = [];

    if(isset($items)):
        foreach($items as $item):
            $obj = new stdClass();
            $obj->custom = $item;

            array_push($array_of_objs, $obj);
        endforeach;
    endif;

    return $array_of_objs;
}


// function convert_expeditions_to_array($expeditions) {
//   $expeditions_array = [];
//   $values = ['title', 'post_content', 'thumbnail',
//           'external_link', 'is_active', 'start_year',
//           'end_year', 'locations', 'event_type', 'collection_type',
//           'arnold_arboretum_participants', 'other_participants',
//           'other_institutions', 'acquisitions'];

//   foreach($expeditions as $expedition):

//     error_log('var dump: ' . var_dump($expedition));

//     $expedition_array = [];

//     foreach($values as $value):
//       if(isset($expedition->{$value})):
//         $expedition_array[$value] = $expedition->{$value};
//       endif;
//     endforeach;

//     array_push($expeditions_array, $expedition_array);
//   endforeach;

//   return $expeditions_array;
// }
/**
 * Helper function to add array of Objects to another array of Objects
 * A field returns an array of arrays, but a repository returns an array of objects
 *
 * @param Array of objects 1
 * @param Array of objects 2
 * @return Array of objects
 */
// function concat_array_of_objects($array_of_objects_1, $array_of_objects_2) {
//   $array_of_objs = [];

//   foreach($array_of_objects_1 as $array):
//       $obj = (object) $array;
//       array_push($array_of_objs, $obj);
//   endforeach;

//   foreach($array_of_objects_2 as $array):
//     $obj = (object) $array;
//     array_push($array_of_objs, $obj);
//   endforeach;

//   return $array_of_objs;
// }

// function to_array_of_array($arrayOfObjects) {
//     if (is_array($data) || is_object($data))
//     {
//         $result = array();
//         foreach ($data as $key => $value)
//         {
//             $result[$key] = object_to_array($value);
//         }
//         return $result;
//     }
//     return $data;
// }

/**
 * Outdated - sort events into decades and centuries
 *
 * function getCentury($decade) {
 *   return intval(floor(($decade) / 100) + 1);
 * }
 *
 * function getEventsByCentury($eventsByDecades) {
 *   $decadesByCentury = array();
 *   $previousCentury = 0;
 *   $decadeOfEvents = array();
 *
 *   forEach($eventsByDecades as $decade => $events) {
 *     $century = getCentury($decade);
 *     if($previousCentury != $century) {
 *       $previousCentury = $century;
 *       $decadeOfEvents = array();
 *     }
 *     if(array_key_exists($century, $decadesByCentury)) {
 *       $decadeOfEvents[$decade] = $events;
 *       $decadesByCentury[$century] = $decadeOfEvents;
 *     } else {
 *       $decadeOfEvents[$decade] = $events;
 *       $decadesByCentury[$century] = $decadeOfEvents;
 *     }
 *   }
 *
 *   krsort($decadesByCentury);
 *   return $decadesByCentury;
 * }
 *
 * function getDecade($year) {
 *   return intval(floor($year/ 10) * 10);
 * }
 *
 * function getEventsByDecade($events) {
 *   $eventsByDecade = array();
 *   forEach($events as $event) {
 *     $decade = getDecade($event['start_year']);
 *     if(array_key_exists($decade, $eventsByDecade)) {
 *       array_push($eventsByDecade[$decade], $event);
 *     } else {
 *       $eventsByDecade[$decade] = [$event];
 *     }
 *   }
 *
 *   return sortEvents($eventsByDecade);
 * }
 */


 
/**
 * 
 */
// Give Editors the ability to see ninja forms
// Must use all three filters for this to work properly.
add_filter('ninja_forms_admin_parent_menu_capabilities',   'nf_subs_capabilities'); // Parent Menu
add_filter('ninja_forms_admin_all_forms_capabilities',     'nf_subs_capabilities'); // Forms Submenu
add_filter('ninja_forms_admin_submissions_capabilities',   'nf_subs_capabilities'); // Submissions Submenu

function nf_subs_capabilities($cap) {
    return 'edit_posts'; // EDIT: User Capability
}


/**
 * Call to see if the language translantions dropdown should appear
 */
function check_for_translations() {
    global $post;
    $is_translated = apply_filters('wpml_element_has_translations', NULL, $post->ID, 'page');

    if(!$is_translated) {
        return 'hidden';
    }
}
  
/** 
 * Set up the Ajax Logout 
 */
function utilities_scripts_enqueuer() {
    global $wp;
    wp_enqueue_script('jquery');

    // New User Registration  -- might be able to get rid of this
    wp_register_script('new-user-registration', ARBORETUM_CUSTOM_URL . 'js/new-user-registration.js', array('jquery'));
    wp_localize_script('new-user-registration', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('new-user-registration');
    
    // Ticket Cancelation
    wp_register_script('ticket-cancelation', ARBORETUM_CUSTOM_URL . 'js/ticket-cancelation.js', array('jquery'));
    wp_localize_script('ticket-cancelation', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('ticket-cancelation');

    // Event Registration
    wp_register_script('event-registration', ARBORETUM_CUSTOM_URL . 'js/event-registration.js', array('jquery'));
    wp_localize_script('event-registration', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('event-registration');

    // Event Map
    wp_register_script('event-map', ARBORETUM_CUSTOM_URL . 'js/event-map.js', array('jquery'));
    wp_localize_script('event-map', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('event-map');

    // Logout
    wp_register_script('logout-js', ARBORETUM_CUSTOM_URL . 'js/logout.js', array('jquery'));
    $current_url = add_query_arg($_SERVER['QUERY_STRING'], '', home_url($wp->request));//home_url( $wp->request );
    
    if(!isset($_COOKIE['current_url'])) {
        setcookie('current_url', $current_url, time() + 360000);
    }

    wp_localize_script('logout-js', 'arbAjaxLogout',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'home_url' => $current_url,
            'logout_nonce' => wp_create_nonce('ajax-logout-nonce'),
        )
    );
    wp_enqueue_script('logout-js');
}
add_action('wp_enqueue_scripts', 'utilities_scripts_enqueuer');
add_action('wp_ajax_ajaxlogout', 'custom_ajax_logout_func');

function custom_ajax_logout_func(){
    //check_ajax_referer( 'ajax-logout-nonce', 'ajaxsecurity' );
    wp_logout();
    //ob_clean(); // probably overkill for this, but good habit
    //wp_send_json_success();
}
  

/**
 * Extend AFC DatePicker field range
 */
function extend_afc_date_picker_range() {    
  wp_register_script('date-picker-js', ARBORETUM_CUSTOM_URL . 'js/custom-date-picker.js', array('jquery'));
  wp_localize_script('date-picker-js', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

  wp_enqueue_script('date-picker-js', '', array(), false, true);
}
add_action('admin_enqueue_scripts', 'extend_afc_date_picker_range');


  

/**
 * Create the tokens to hold the widget information that will get populated by JS when it reads 'js-solar-widget' class
 */
function get_solar_data($atts) {
    $filename = $_SERVER['DOCUMENT_ROOT'] . '/solar_data.json'; // $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/arboretum-custom/solar_data.json';
    $file = file_get_contents($filename);
    $json = json_decode($file);
  
    $output =
        '<div class="js-solar-widget">
        <div class="solar-intro"></div>
        <div class="solar-systems">';
  
    foreach($json->systems as $system):
        $format = '<div class="solar-system" data-system-name="%s" data-total="%s" data-start-date="%s"></div>';
        $output .=  sprintf($format, $system->system_name, $system->total, $system->start_date);
    endforeach;
  
    $output .= '</div></div>';
  
    echo $output;
}
add_shortcode('get_solar_data', 'get_solar_data');


/**
 * Pulls in the Algolia json file and returns it
 */
function get_algolia_data() {
    $filename = $_SERVER['DOCUMENT_ROOT'] . '/algolia_calc.json';
    $file = file_get_contents($filename);
    $json = json_decode($file, true);

    return $json;
}

/**
 * Add GoogleMaps to ACF
 */
function arboretum_acf_init() {
    acf_update_setting('google_api_key', 'AIzaSyDfdSX90Sc8q7ozRMEnX3YjEK0LLhd6DpQ');
}
add_action('acf/init', 'arboretum_acf_init');

function acf_google_map_api( $api ){
	$api['key'] = 'AIzaSyDfdSX90Sc8q7ozRMEnX3YjEK0LLhd6DpQ';
	return $api;
}
add_filter('acf/fields/google_map/api', 'acf_google_map_api');


/**
 *  Redefine user new user registration email
 */
// if ( !function_exists('wp_new_user_notification') ) {
//     function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
//         $user = new WP_User($user_id);

//         $user_login = stripslashes($user->user_login);
//         $user_email = stripslashes($user->user_email);

//         $message  = "New user registration for the Arnold Arboretum website\r\n\r\n";
//         $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
//         $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

//         wp_mail(get_option('admin_email'), 'New User Registration', $message);

//         if ( empty($plaintext_pass) )
//             return;

//         $message  = __('Hi there,') . "\r\n\r\n";
//         $message .= "Welcome to the Arnold Arboretum. Here is how to log in:\r\n\r\n";
//         $message .= wp_login_url() . "\r\n";
//         $message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
//         $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n";
//         $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "\r\n\r\n";
//         $message .= __('Adios!');

//         wp_mail($user_email, 'Arnold Arboretum.  Your username and password', $message);
//         wp_mail(get_option('admin_email'), 'Arnold Arboretum.  Your username and password', $message);
//         wp_mail('matt.caulkins@gmail.com', 'Arnold Arboretum.  Your username and password', $message);
//     }
// }

/**
 * Redirect to a new login page
 */
// function redirect_login_page() {
//     $login_url  = home_url( '/login' );
//     $url = basename($_SERVER['REQUEST_URI']); // get requested URL
//     isset( $_REQUEST['redirect_to'] ) ? ( $url   = "wp-login.php" ): 0; // if users ssend request to wp-admin
//     if( $url  == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET')  {
//         wp_redirect( $login_url );
//         exit;
//     }
// }
// add_action('init','redirect_login_page');

// /**
//  * Failed login error handling
//  */
// function error_handler() {
//     $login_url  = home_url( '/login' );
//     global $errors;
//     $err_codes = $errors->get_error_codes(); // get WordPress built-in error codes
//     $_SESSION["err_codes"] =  $err_codes;
//     wp_redirect( $login_url ); // keep users on the same page
//     exit;
// }
// add_filter( 'login_errors', 'error_handler');

function correct_admin_email($email) {
    return "admin@arnarb.harvard.edu";
}
add_filter('wp_mail_from', 'correct_admin_email');


/**
 * Register a new user
 */

function arboretum_new_user_registration_callback() {
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = wp_create_user($email, $password, $email);

    if (is_int($result)) {
        // add the user details
    } else {
        echo json_encode($result);
    }
}

add_action('wp_ajax_arboretum_new_user_registration', 'arboretum_new_user_registration_callback');
add_action('wp_ajax_nopriv_arboretum_new_user_registration', 'arboretum_new_user_registration_callback');


/**
 * Send an email to admin when a post is updated
 */
function post_saved_notification($post_ID) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>'
    );

    $post_type = get_post_type($post_ID);
    $title = get_the_title($post_ID);
    $url = $_SERVER["HTTP_HOST"] . '/wp-admin/post.php?post=' . $post_ID . '&action=edit&lang=en';

    $to                 = get_option('admin_email');
    $subject            = 'New ' . $post_type . ' saved';
    $body               = $post_type . '<br><br>' . $url . '<br><br><a href="'. $url . '">' . $title . '</a><br><br>';

    wp_mail($to, $subject, $body, $headers);
}

add_action('save_post', 'post_saved_notification', 10, 3);

/**
 * Send an email to admin when a post is updated
 */
function post_update_notification($post_ID, $post_after, $post_before) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

    $headers = array(
        "Content-Type: text/html; charset=UTF-8\r\n",
        'From: The Arnold Arboretum <admin@arnarb.harvard.edu>'
    );

    $post_type = get_post_type($post_ID);
    $title = get_the_title($post_ID);
    $url = $_SERVER["HTTP_HOST"] . '/wp-admin/post.php?post=' . $post_ID . '&action=edit&lang=en';

    $to                 = get_option('admin_email');
    $subject            = 'New ' . $post_type . ' update';
    $body               = $post_type . '<br><br>' . $url . '<br><br><a href="'. $url . '">' . $title . '</a><br><br>';

    wp_mail($to, $subject, $body, $headers);
}

add_action('post_updated', 'post_update_notification', 10, 3);