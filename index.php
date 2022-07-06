<?php
/*
Plugin Name: Arboretum Custom
Description: Custom functions for Arboretum website
Version: 0.1.2
Author: Arnold Arboretum
*/
use Arboretum\Repositories\DirectorRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

define('ARBORETUM_CUSTOM', plugin_dir_url( __FILE__ ));

/**
 * Edit login page
 */
function arb_login_logo_url() {
  return home_url();
}
add_filter('login_headerurl', 'arb_login_logo_url');

function arb_login_logo_url_title() {
  return 'Arnold Arboretum';
}
add_filter('login_headertext', 'arb_login_logo_url_title');

/**
 * Add login form shortcode
 */
function arb_log_me_shortcode() {
  $args = array(
    'echo'            => true,
    'label_username' => __( 'Email Address' ),
    // 'redirect'        => get_permalink( get_the_ID() ),
    'remember'        => true,
    'value_remember'  => true,
  );

  return '<div class="arb-form">' . wp_login_form( $args ) . '</div>';
}

add_shortcode( 'arb_log_me', 'arb_log_me_shortcode' );

/**
 * Add a Members tab to the Memberpress navigation
 */
// function mepr_add_user_tabs($user) {
// ? >
//   <span class="mepr-nav-item members">
//     <a href="/user-history/">History</a>
//   </span>
//   <span class="mepr-nav-item members">
//     <a href="/members/">Members Area</a>
//   </span>
// < ?php
// }

// add_action('mepr_account_nav', 'mepr_add_user_tabs');

/**
 * Finds if the member is active or inactive right now
 */
// function get_mepr_status() {
//   $mepr_options = MeprOptions::fetch();
//   $mepr_user = MeprUtils::get_currentuserinfo();

//   if ($mepr_user && $mepr_user->is_active()) {
//       return true;
//   }
//   return false;
// }

// function is_protected_by_mepr_rule() {
//   $mepr_options = MeprOptions::fetch();
//   return MeprRule::is_locked(get_post());
// }


/**
 * Add a wildcard filter to allow for nested repeater queries
 */
// function allow_wildcards($where) {
//   $where = str_replace(
//       "meta_key LIKE 'registrants_$",
//       "meta_key = 'registrants_%",
//       $where
//   );
//   return $where;
// }

// add_filter('posts_where', 'allow_wildcards');


/**
 * Add google APIs for maps to work
 *
 * // Method 1: Filter.
 * function my_acf_google_map_api( $api ){
 *  $api['key'] = 'xxx';
 *  return $api;
 * }
 * add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
 *
 * // Method 2: Setting.
 * function my_acf_init() {
 *  acf_update_setting('google_api_key', 'xxx');
 * }
 *
 * add_action('acf/init', 'my_acf_init');
 */




/**
 * Register event scripts
 */
add_action('wp_enqueue_scripts', 'event_scripts_enqueuer');

function event_scripts_enqueuer() {
  wp_register_script('event-registration', ARBORETUM_CUSTOM . '/js/event-registration.js', array('jquery'));
  wp_localize_script('event-registration', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

  wp_register_script('ticket-cancelation', ARBORETUM_CUSTOM . '/js/ticket-cancelation.js', array('jquery'));
  wp_localize_script('ticket-cancelation', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

  wp_enqueue_script('jquery');
  wp_enqueue_script('event-registration');
  wp_enqueue_script('ticket-cancelation');
}


add_action('wp_ajax_arboretum_event_registration', 'arboretum_event_registration_callback');
add_action("wp_ajax_arboretum_ticket_cancelation", "arboretum_ticket_cancelation");

/**
 * Handle event registration form submission
 *
 * @tag event_registration
 * @callback @event_registration_callback
 */

/**
 * @param $form_data array
 * @return void
 */
function arboretum_event_registration_callback() {
  // if(!wp_verify_nonce($_POST['nonce'], "event_registration_nonce_" . $_POST['event_id'])) {
  //   exit ("No naughty business" . $_POST['nonce'] . ' event id: ' . $_POST['event_id'] . ' user id: ' . $_POST['user_id']);
  // }

  // echo $_REQUEST;
          // $time_canceled = get_post_meta($_REQUEST["event_id"], "time_canceled", true);

          // date_default_timezone_set('America/New_York');
          // $date = date("Y-m-d H:i:s");

          // $response = update_post_meta($_REQUEST['ticket_id'], 'time_canceled', $date);

          // if($response === false) {
          //   $result['type'] = "error";
          //   $result['time_canceled'] = $time_canceled;
          // }
          // else {
          //     $result['type'] = "success";
          //     $result['time_canceled'] = $date;
          // }

          // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          //     $result = json_encode($result);
          //     echo $result;
          // }
          // else {
          //     header("Location: ".$_SERVER["HTTP_REFERER"]);
          //     echo $result;
          // }
          // // error_log(var_dump($args));
          // // $raw_data = file_get_contents('php://input');
          // // parse_str($raw_data, $data_array);
          // // $data = (object)$data_array;
          // // // $json_data =  json_encode((array) $obj);

          // // // This is the response returned to the client
          // // echo 'Raw: ' . var_dump($data_array);
          // // echo 'Obj: ' . var_dump($data);

          // date_default_timezone_set('America/New_York');
          // $date = date("Y-m-d H:i:s");


          // $user_data = [];
          // $email_data = "\n";
          // foreach ($data as $key => $val) {
          //   $user_data[$key] = $val;
          //   $email_data .= $key . ": " . $val . "\n";
          // }

          // $requested = $user_data["requested"];
          // $event_id = $user_data["eventId"];
          // $event = new Event($event_id);

          // $user_id = $user_data["userId"];
          // $user = new User($user_id);

          // $anchor_tag = $event->anchor_tag;
          // $registrants = $event->registrants;
          // $current_num_registants = 0;
          // if (is_array($registrants)) {
          //   $current_num_registants = count($registrants);
          // }

  date_default_timezone_set('America/New_York');
  $date = date("Y-m-d H:i:s");

  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

  $recipient = $_POST['email'];
  $requested = $_POST['requested'];
  $user_id = $_POST['user'];
  $user = new User($user_id);

  $event_id = $_POST['event'];
  $event = new Event($event_id);

  $email_data .= 'Number of tickets requested: ' . $requested;
  $email_data .= "\nAvailability left: " . $_POST['availability'] . '  USER: ' . $user_id . '   EVENT: ' . $event_id . '    RECIPIENT: ' . $recipient;

  // Send notification of new registrant
  $to                 = 'matthew_caulkins@harvard.edu';
  $subject            = 'New Event Registration BY AJAX';
  $body               = $email_data;

  wp_mail($to, $subject, $body, $headers);

  if(!empty($event->start_date)) {
    $event_date = date('Y-m-d at H:i:s', $event->start_date);
    $event_time = date('H:i', $event->start_date);
  } else {
    $x = 0;
    while(date('Y-m-d H:i:s', intval($event->event_dates[$x])) < $date) {
      $x++;
    }
    $event_date = date('Y-m-d', $event->event_dates[$x]);
    $event_time = date('H:i', $event->event_dates[$x]);
  }

  // Send confirmation email
  $to                 = $recipient;
  $subject            = 'Confirmation to ' . $event->title;

  // if()
  $body               = "Thank you for registering for " . $event->title . " on " . $event_date . " at " . $event_time . ". If you have any questions, please email us at <a href='publicprograms@arnarb.harvard.edu'>publicprograms@arnarb.harvard.edu</a> or call us at <a href='tel:617-384-5209'>(617) 384-5209</a>.";
  $body               .= "<br><br>We welcome people of all abilities and are committed to facilitating a safe and engaging experience for all who visit. To request services such as an interpreter, wheelchair, or other assistance prior to attending an event, please contact us.";


// <Directions>

// <Event Description>

// Your registration details are below:

// <all of the fields that the person filled in>

  wp_mail($to, $subject, $body, $headers);
  // Send their confirmation email


  $response = '';

  for ($i = 0; $i < $requested; $i++) {
    // insert the post and set the category
    $ticket_id = wp_insert_post(
      array (
        'post_type' => 'ticket',
        'post_title' => $user->display_name . ': ' . $event->title,
        'post_status' => 'publish',
        'meta_input' => array(
          'user' => $user_id,
          'event' => array(
            $event_id
          ),
          'time_registered' => $date,
        )
      )
    );

    $response .= $ticket_id . ', ';
  }

  if($response === false) {
    $result['type'] = "error";
    $result['data'] = $body;
  }
  else {
    $result['type'] = "success";
    $result['ticket_ids'] = $response;
  }

  if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $result = json_encode($result);
    echo var_dump($result);
  }
  else {
    header("Location: ".$_SERVER["HTTP_REFERER"]);
    echo var_dump($result);
  }

  date_default_timezone_set('UTC');
  die;
}


// AJAX hooks for event registration (replace my_action with POST / GET value)   // my_action
/// add_action("wp_ajax_nopriv_arboretum_ticket_cancelation", "arboretum_ticket_cancelation");   // my_action    - They will need to be logged in to see this anyway


function arboretum_ticket_cancelation() {
  // if(!wp_verify_nonce($_POST['nonce'], "cancel_ticket_nonce_" . $_POST['ticket_id'])) {
  //   exit ("No naughty business" . $_POST['nonce'] . ' ticket id: ' . $_POST['ticket_id']);
  // }

  $time_canceled = get_post_meta($_POST["ticket_id"], "time_canceled", true);

  date_default_timezone_set('America/New_York');
  $date = date("Y-m-d H:i:s");

  $response = update_post_meta($_POST['ticket_id'], 'time_canceled', $date);

  if($response === false) {
    $result['type'] = "error";
    $result['time_canceled'] = $time_canceled;
  }
  else {
    $result['type'] = "success";
    $result['time_canceled'] = $date;
  }

  if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $result['if-else'] = 'HTTP_X_REQUESTED_WITH NOT EMPTY AND = xmlhttprequest';
    $result = json_encode($result);
      echo $result;
  }
  else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
      echo $result;
  }


  $to                 = 'matthew_caulkins@harvard.edu';
  $subject            = 'Cancel Fired BY NONCE AJAX APPROACH';
  $body               = $response . '    ' . $result;
  wp_mail($to, $subject, $body, $headers);

  date_default_timezone_set('UTC');
  die();
}





/**
 * Adding custom columns to the admin section for Tickets
 */
add_filter('manage_ticket_posts_columns', 'set_custom_ticket_columns');

function set_custom_ticket_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['user'] = __('User', 'arboretum');
  $columns['event'] = __('Event', 'arboretum');
  $columns['time_registered'] = __('Time Registered', 'arboretum');
  $columns['time_attended'] = __('Time Attended', 'arboretum');
  $columns['canceled'] = __('Canceled', 'arboretum');
  $columns['date'] = __('Date', $date);

  return $columns;
}

add_action('manage_ticket_posts_custom_column', 'custom_ticket_column', 10, 2);

function custom_ticket_column($column, $post_id) {
  switch ($column) {

    case 'user':
      $user_id = get_field('user', $post_id);
      $user = new User($user_id);

      echo $user->first_name . ' ' . $user->last_name;
      break;

    case 'event':
      $events = '';

      $event_ids = get_field('event', $post_id);
      $num = count($event_ids);
      $i = 0;

      foreach($event_ids as $key => $event_id) {
        $event = new Event($event_id);
        $events .= '<a href="/wp-admin/edit.php?post_type=ticket/">' . $event->title . '</a>';

        if(++$i != $num) {
          $events .= ', ';
        } //$event->title . ', ';
      }

      echo $events; //$events;
      break;

    case 'time_registered':
      echo get_field('time_registered', $post_id);
      break;

    case 'time_attended':
      echo get_field('time_attended', $post_id);
      break;

    case 'canceled':
      $time_canceled = get_field('time_canceled', $post_id);
      if(isset($time_canceled) && $time_canceled != '') {
        echo 'Canceled on ' . $time_canceled;
      }
      break;
  }
}

/**
 * Make custom columns sortable
 */
add_filter('manage_edit-ticket_sortable_columns', 'set_custom_ticket_sortable_columns');

function set_custom_ticket_sortable_columns( $columns ) {
  $columns['user'] = 'user';
  $columns['event'] = 'event';
  $columns['time_registered'] = 'time_registered';
  $columns['time_attended'] = 'time_attended';
  $columns['canceled'] = 'time_canceled';

  return $columns;
}

add_action('pre_get_posts', 'ticket_orderby');

function ticket_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('user' == $orderby) {
    $query->set('meta_key', 'user');
    $query->set('orderby', 'meta_value');
  } else if('event' == $orderby) {
    $query->set('meta_key', 'event');
    $query->set('orderby', 'meta_value');
  } else if('time_registered' == $orderby) {
    $query->set('meta_key', 'time_registered');
    $query->set('orderby', 'meta_value');
  } else if('time_attended' == $orderby) {
    $query->set('meta_key', 'time_attended');
    $query->set('orderby', 'meta_value');
  } else if('time_canceled' == $orderby) {
    $query->set('meta_key', 'time_canceled');
    $query->set('orderby', 'meta_value');
  }
}

/**
 * Add filter dropdowns for tickets
 */
add_action('restrict_manage_posts', 'ticket_filters_restrict_manage_posts');
/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @author Ohad Raz
 *
 * @return void
 */
function ticket_filters_restrict_manage_posts($post_type){
    global $wpdb, $table_prefix;

    $type = 'ticket';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if('ticket' !== $type) {
      return;
    }

    $tickets = get_posts(array('numberposts' => -1, 'post_type' => 'ticket', 'posts_per_page' => -1));

    // User column
    $values = array();
    foreach($tickets as $ticket) {
      setup_postdata($ticket);
      $user_id = get_field('user', $ticket->ID);
      $user = new User($user_id);

      $name = $user->first_name . ' ' . $user->last_name;
      $values[$user_id] = $name;
      wp_reset_postdata();
    }
  ?>
    <select name="ticket_user_filter">
    <option value=""><?php _e('All users', 'ticket'); ?></option>
  <?php
    $current_v = isset($_GET['ticket_user_filter'])? $_GET['ticket_user_filter']:'';
    foreach ($values as $label => $value) {
      printf
        (
          '<option value="%s"%s>%s</option>',
          $label,
          $label == $current_v? ' selected="selected"':'',
          $value
        );
    }
  ?>
    </select>
  <?php
    // Event column
    $values = array();
    foreach($tickets as $ticket) {
      setup_postdata($ticket);
      $event_ids = get_field('event', $ticket->ID);
      foreach($event_ids as $event_id) {
        $event = new Event($event_id);
        $values[$event_id] = $event->title;
      }
      wp_reset_postdata();
    }
  ?>
    <select name="ticket_event_filter">
    <option value=""><?php _e('All events', 'ticket'); ?></option>
  <?php
    $current_v = isset($_GET['ticket_event_filter'])? $_GET['ticket_event_filter']:'';
    foreach ($values as $label => $value) {
      printf
        (
          '<option value="%s"%s>%s</option>',
          $label,
          $label == $current_v? ' selected="selected"':'',
          $value
        );
    }
  ?>
    </select>
  <?php
    wp_reset_postdata();
}


add_filter('parse_query', 'ticket_filters');
/**
 * if submitted filter by post meta
 *
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function ticket_filters($query){
    global $pagenow;

    $type = 'ticket';
    if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
    }
    if('ticket' !== $type) {
      return;
    }

    // User filter
    if (is_admin() &&
      $pagenow=='edit.php' &&
      isset($_GET['ticket_user_filter']) &&
      $_GET['ticket_user_filter'] != '' &&
      $query->is_main_query()
    ) {
      $query->query_vars['meta_query'][] = array(
        'key' => 'user',
        'value' => $_GET['ticket_user_filter'],
        'compare' => '='
      );
    }

    // // Event filter
    if (is_admin() &&
      $pagenow=='edit.php' &&
      isset($_GET['ticket_event_filter']) &&
      $_GET['ticket_event_filter'] != ''
      && $query->is_main_query()
    ) {
      $query->query_vars['meta_query'][] = array(
        'key' => 'event',
        'value' => '"'.$_GET['ticket_event_filter'].'"',
        'compare' => 'LIKE'
      );
    }

    // return $query;
}



/**
 * Adds the download option in bulk actions
 */
add_filter('bulk_actions-edit-ticket', 'register_generate_spreadsheet_bulk_action');

function register_generate_spreadsheet_bulk_action($bulk_actions) {
  $bulk_actions['download_tickets'] = __('Download Tickets', 'download_tickets');
  return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-ticket', 'generate_spreadsheet_bulk_action', 10, 3);

function generate_spreadsheet_bulk_action($redirect_url, $action, $post_ids) {
  $date = date("Y-m-d");

  if($action == 'download_tickets') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setTitle("Event Registrations $date");
    $tickets = get_posts(
      array(
        'numberposts' => -1,
        'include' => $post_ids,
        'post_type' => 'ticket'
      )
    );

    $num = 1;
    $sheet->setCellValue("A1", "Title");
    $sheet->setCellValue("B1", "Time Registered");
    $sheet->setCellValue("C1", "User Name");
    $sheet->setCellValue("D1", "User Email");
    $sheet->setCellValue("E1", "City");
    $sheet->setCellValue("F1", "State");
    $sheet->setCellValue("G1", "Country");
    $sheet->setCellValue("H1", "Zip");
    $sheet->setCellValue("I1", "Event Title");
    $sheet->setCellValue("J1", "Start Date");
    $sheet->setCellValue("K1", "Locations");

    foreach($tickets as $ticket) {
      $num ++;
      $sheet->setCellValue("A$num", $ticket->post_title);
      $sheet->setCellValue("B$num", $ticket->time_registered);

      $user = get_user_by('ID', $ticket->user);
      
      $sheet->setCellValue("C$num", "$user->first_name $user->last_name");
      $sheet->setCellValue("D$num", $user->user_email);
      $sheet->setCellValue("E$num", $user->city);
      $sheet->setCellValue("F$num", $user->state);
      $sheet->setCellValue("G$num", $user->country);
      $sheet->setCellValue("H$num", $user->zip);

      // Consolidate event data into one string for entry into spreadsheet
      $n = 0;
      $count = count($ticket->event);
      $titles = '';
      $dates = '';
      $locations = '';

      foreach($ticket->event as $event_id){
        $n ++;

        $event = new Event($event_id);
        $titles .= $event->title;
        $dates .= $event->start_date;

        $l = 0;
        $location_count = count($event->locations);
        foreach($event->locations as $location_id) {
          $l ++;
          $location = new Location($location_id);

          $locations .= $location->title;

          if($l < $location_count) {
            $locations .= ', ';
          }
        }
        if($n < $count) {
          $titles .= '; ';
          $dates .= '; ';
          $locations .= '; ';
        }
      }

      $sheet->setCellValue("I$num", $titles);
      $sheet->setCellValue("J$num", $dates);
      $sheet->setCellValue("K$num", $locations);
    }

    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"Event-Registrations-$date.xlsx\"");
    header("Cache-Control: max-age=0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");
    $writer->save("php://output");
  }
// 
  return $redirect_url;
}

/**
 * Adding custom columns to the admin section for Art Shows
 */
add_filter('manage_art_show_posts_columns', 'set_custom_art_show_columns');

function set_custom_art_show_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['start_date'] = __('Start Date', 'arboretum');
  $columns['end_date'] = __('End Date', 'arboretum');
  $columns['date'] = __('Date', $date);

  return $columns;
}

add_action('manage_art_show_posts_custom_column' , 'custom_art_show_column', 10, 2);

function custom_art_show_column($column, $post_id) {
  switch ($column) {

    // display a start date
    case 'start_date':
      echo get_field('start_date', $post_id);
      break;

    case 'end_date':
      echo get_field('end_date', $post_id);
      break;
  }
}

add_filter('manage_edit-art_show_sortable_columns', 'set_custom_art_show_sortable_columns');

function set_custom_art_show_sortable_columns( $columns ) {
  $columns['start_date'] = 'start_date';
  $columns['end_date'] = 'end_date';

  return $columns;
}

add_action('pre_get_posts', 'art_show_orderby');

function art_show_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('start_date' == $orderby) {
    $query->set('meta_key', 'start_date');
    $query->set('orderby', 'meta_value');
  } else if('end_date' == $orderby) {
    $query->set('meta_key', 'end_date');
    $query->set('orderby', 'meta_value');
  }
}

/**
 * Adding custom columns to the admin section for Expeditions
 */
add_filter('manage_expedition_posts_columns', 'set_custom_expedition_columns');

function set_custom_expedition_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['start_year'] = __('Start Year', 'arboretum');
  $columns['end_year'] = __('End Year', 'arboretum');
  $columns['is_active'] = __('Is Active', 'arboretum');
  $columns['date'] = __('Date', $date);

  return $columns;
}

add_action('manage_expedition_posts_custom_column' , 'custom_expeditions_column', 10, 2);

function custom_expeditions_column($column, $post_id) {
  switch ($column) {

    case 'start_year':
      echo get_field('start_year', $post_id);
      break;

    case 'end_year':
      echo get_field('end_year', $post_id);
      break;

    case 'is_active':
      echo get_field('is_active', $post_id);
      break;
  }
}

add_filter('manage_edit-expedition_sortable_columns', 'set_custom_expedition_sortable_columns');

function set_custom_expedition_sortable_columns( $columns ) {
  $columns['start_year'] = 'start_year';
  $columns['end_year'] = 'end_year';
  $columns['is_active'] = 'is_active';

  return $columns;
}

add_action('pre_get_posts', 'expedition_orderby');

function expedition_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('start_year' == $orderby) {
    $query->set('meta_key', 'start_year');
    $query->set('orderby', 'meta_value');
  } else if('end_year' == $orderby) {
    $query->set('meta_key', 'end_year');
    $query->set('orderby', 'meta_value');
  } else if('is_active' == $orderby) {
    $query->set('meta_key', 'is_active');
    $query->set('orderby', 'meta_value');
  }
}

/**
 * Adding custom columns to the admin section for Plant Bios
 */
add_filter('manage_plant_posts_columns', 'set_custom_plant_columns');

function set_custom_plant_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['introduction_date'] = __('Introduction Date', 'arboretum');
  $columns['date'] = __('Date', $date);

  return $columns;
}

add_action('manage_plant_posts_custom_column' , 'custom_plant_column', 10, 2);

function custom_plant_column($column, $post_id) {
  switch ($column) {

    // display a introduction date
    case 'introduction_date':
      echo get_field('introduction_date', $post_id);
      break;
  }
}

add_filter('manage_edit-plant_sortable_columns', 'set_custom_plant_sortable_columns');

function set_custom_plant_sortable_columns( $columns ) {
  $columns['introduction_date'] = 'introduction_date';

  return $columns;
}

add_action('pre_get_posts', 'plant_orderby');

function plant_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('introduction_date' == $orderby) {
    $query->set('meta_key', 'introduction_date');
    $query->set('orderby', 'meta_value');
  }
}




/////////////////////////////////////////
function sort_shows_and_events($events) {
  $format = 'Y-m-d H:i:s';
  $posts = array();

  foreach($events as $event) {
      if($event->start_date) {
        $event->start_date = date($format, strtotime($event->start_date)); // substr($event->start_date, 0, 4) . '-' . substr($event->start_date, 4, 2) . '-' . substr($event->start_date, 6) . ' 00:00:00'; // 23:59:59
      }
      if($event->end_date) { //} && !strpos($event->end_date, '-')) {
        $event->end_date = date($format, strtotime($event->end_date)); // substr($event->end_date, 0, 4) . '-' . substr($event->end_date, 4, 2) . '-' . substr($event->end_date, 6) . ' 00:00:00'; // 23:59:59
      }

      // sort the event_dates

      // Check for the next date in event_dates array
      if($event->event_dates) {
        $event_dates = array();
        for($i = 0; $i < $event->event_dates; $i++) {
          $date = 'event_dates_' . $i . '_date';
          array_push($event_dates, date($format, strtotime($event->$date)));
        }

        usort($event_dates, function($a, $b)
        {
          if ($a == $b):
            return (0);
          endif;

          return (($a > $b) ? 1 : -1);
        });


        for($i = 0; $i < $event->event_dates; $i++) {
          // $event->dates_has = $event->event_dates;
          // $date = 'event_dates_' . $i . '_date';
          // $date_text = 'event_' . $i . '_date';

          if(!$event->start_date) {
            if($event_dates[$i] > date($format)) {
              $event->start_date = date($format, strtotime($event_dates[$i]));
            }
          }

          // $event->$date_text = $event->$date;
        }

        // $date = 'event_dates_' . ($event->event_dates - 1) . '_date';
        $event->end_date = $event_dates[count($event_dates) - 1];
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
    usort($items, function($a, $b) use ($sort_value)
    {
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

function get_era($year) {
  if ($year <= 1927) {
    return "1874-1927";
  } elseif ($year <= 1976) {
    return "1928-1976";
  } else {
    return "1977-present";
  }
}

/**
 * Get an array of arrays sorted into 3 eras, 1874-1927, 1928-1976, 1977-present
 */
function get_items_by_era($items, $sort_value = "start_year") {
  $items_by_era = array();

  foreach($items as $item) {
    $era = get_era($item->custom[$sort_value]);

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
 * Create a shortcode for director's lectures
 */
function directors_reports($atts) {
  $context = Timber::context();

  $page = Timber::get_post();
  $context['page'] = $page;

  $directorRepo = new DirectorRepository();
  $context['directors'] = $directorRepo->getDirectors()->get();

  ob_start();
  ?>
    <div class="cleanfix">
  <?php
    Timber::render('components/directors-reports-accordion.twig', $context);
  ?>
    </div>
  <?php
  return ob_get_clean();
}

add_shortcode('directors_reports', 'directors_reports');





// Give Editors the ability to see ninja forms
// Must use all three filters for this to work properly.
add_filter('ninja_forms_admin_parent_menu_capabilities',   'nf_subs_capabilities'); // Parent Menu
add_filter('ninja_forms_admin_all_forms_capabilities',     'nf_subs_capabilities'); // Forms Submenu
add_filter('ninja_forms_admin_submissions_capabilities',   'nf_subs_capabilities'); // Submissions Submenu

function nf_subs_capabilities($cap) {
  return 'edit_posts'; // EDIT: User Capability
}








/**
 * Handle waitlist form submission.
 * Depricated: will be handled by event registration
 *
 * @tag waitlist_registration
 * @callback waitlist_registration_callback
 */
add_action('waitlist_registration', 'waitlist_registration_callback');

/**
 * @param $form_data array
 * @return void
 */
function waitlist_registration_callback($form_data) {
  $form_id            = $form_data['form_id'];
  $form_fields        = $form_data['fields'];

  foreach ($form_fields as $field):
    $field_id         = $field['id'];
    $field_key        = $field['key'];
    $field_value      = $field['value'];


    // Example Field Key comparison
    if ($field['key'] == 'id'):
      $ID             = $field['value'];
      $contacts       = '';

      $args           = array('p' => $ID, 'post_type' => 'event');
      $loop           = new WP_Query($args);

      while ($loop->have_posts()):
        $loop->the_post();
        global $post;

        $title        = html_entity_decode(get_the_title($post->ID));
        $recipients   = html_entity_decode(implode(', ', get_field('sold_out_contact')));
      endwhile;
    endif;

    $values[$field['key']] = html_entity_decode($field['value']);
  endforeach;

  $to                 = $recipients . ',matthew_caulkins@harvard.edu';
  $subject            = $title . ' sold out waitlist request.';
  $body               = $values['firstname'] . ' ' . $values['lastname'] . ' has joined the waitlist for ' . $title . '.';
  $headers            = array('Content-Type: text/html; charset=UTF-8');

  wp_mail($to, $subject, $body, $headers);
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
 * Extend AFC DatePicker field range
 */
function extend_afc_date_picker_range() {
  wp_enqueue_script('date-picker-js', ARBORETUM_CUSTOM . '/js/custom-date-picker.js', array(), '1.0.0', true);
}

add_action('admin_enqueue_scripts', 'extend_afc_date_picker_range');


/**
 * START DATES FOR EACH SYSTEM
 * HB 8/2016; DGH 12/2016; WH 12/2019
 */

/**
 * Write the solar data to a file for later use
 *
 * @param Array of data
 */
// function write_solar_data($file_name, $data) {
//   $data_file = fopen(`$file_name.json`, 'w');
//   $json_data = json_encode($data);

//   fwrite($data_file, $json_data);
//   fclose($data_file);
// }


// /**
//  * Curl request to Solren for Hunnewell Building
//  */
// function get_hunnewell_solar_data($atts) {
// //   // // create curl resource
// //   // $ch = curl_init();

// //   // // // set url
// //   // curl_setopt($ch, CURLOPT_URL, "http://solrenview.com/xmlfeed/ss-xmlN.php?site_id=4232&ts_start=2021-01-01T00:00:00Z&ts_end=2021-05-11T00:00:00Z&show_whl");

// //   // // //return the transfer as a string
// //   // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// //   // // // $output contains the output string
// //   // $output = curl_exec($ch);

// //   // // // close curl resource to free up system resources
// //   // curl_close($ch);

// //   $url = 'http://solrenview.com/xmlfeed/ss-xmlN.php';// &ts_start=2021-01-01T00:00:00Z&ts_end=2021-05-11T00:00:00Z&show_whl';
// //   //$url = 'http://www.solrenview.com/SolrenView/mainFr.php';//?siteId=4232';//'http://solrenview.com/xmlfeed/ss-xmlN.php';

// //   // $ch = curl_init();
// //   // $timeout = 5;
// //   // curl_setopt($ch, CURLOPT_URL, $url);
// //   // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// //   // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
// //   // $data = curl_exec($ch);
// //   // curl_close($ch);

// //   $data = array(
// //     'site_id' => '4232',
// //     'ts_start' => '2021-01-01T00:00:00Z',
// //     'ts_end' => '2021-05-11T00:00:00Z'
// //   );

// //   $args = array(
// //     'timeout'     => 500000
// //     );

// //   $query_url = $url;// . '?' . http_build_query($data);
// //   $decoded_url = urldecode($query_url);
// //   $response = wp_remote_get($query_url, $args
// // //    array(
// // //      'timeout'     => 20,
// // //      'sslverify' => true,
// //   //    'headers' => array('Content-Type' => 'text/xml;charset=UTF-8')
// //  //   )
// //   );

// //   echo 'query string: ' . $decoded_url . '<br><br>';


// //   if( ! is_wp_error( $response ) ) {
// //     echo 'no errors' . '<br><br>';
// //     if ( is_array( $response )) {
// //       $headers = $response['headers']; // array of http header lines
// //       $body    = $response['body']; // use the content

// //       echo 'body text is: ' . $response . '<br><br>';
// //       echo 'is array.' . '<br><br>';
// //     } else {
// //       echo 'is not array' . '<br><br>';
// //     }
// //   } else {
// //     echo 'there is errors' . '<br><br>';
// //     if( is_wp_error($response)) {

// //       echo $response->get_error_message();
// //     }
// //   }

// //   //echo 'response: ' . $response . '<br><br>';

// // this is the IP address that www.bata.com.sg resolves to
// $server = '209.160.64.80';
// $host   = 'http://solrenview.com/xmlfeed/ss-xmlN.php?site_id=4232';

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $server);

// /* set the user agent - might help, doesn't hurt */
// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
// curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);


// /* try to follow redirects */
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

// /* timeout after the specified number of seconds. assuming that this script runs
// on a server, 20 seconds should be plenty of time to verify a valid URL.  */
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
// curl_setopt($ch, CURLOPT_TIMEOUT, 20);


// $headers = array();
// $headers[] = "Host: $host";

// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

// curl_setopt($ch, CURLOPT_VERBOSE, true);

// /* don't download the page, just the header (much faster in this case) */
// curl_setopt($ch, CURLOPT_NOBODY, true);
// curl_setopt($ch, CURLOPT_HEADER, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


// $response = curl_exec($ch);
// $info = curl_getinfo($ch);
// echo 'Get Info: ';
// var_dump($info);
// echo '<br><br>';

// if($response === false)
// {
//     echo 'Curl error: ' . curl_error($ch) . '<br><br>';
// }
// else
// {
//     echo 'Operation completed without any errors<br><br>';
// }

// curl_close($ch);

// var_dump($response);
// }

// add_shortcode('get_hunnewell_solar_data', 'get_hunnewell_solar_data');


/**
 * Get the Weld Hill and Dana Greenhouse solar data from powerdash
 */
// function get_weld_solar_data($atts) {
//   //$token = get_powerdash_auth_token();

//   //echo 'token is : ' . $token . '<br><br>';

//   //$dana_greenhouse_id = 'c08100e6e3aa95b447ca28d9d2704d21';
//   //$weld_hill_id = '3b0d76fa8540488f31ea060881400d96';
//   // https://api.powerdash.com/v2/systems/3b0d76fa8540488f31ea060881400d96/perfmodels/

//   //https://api.powerdash.com/v2/channeldata/agg/
//   $url = 'https://api.powerdash.com/v2/';
//   $username = 'arboretum_api';
//   $password = "#M^;GJgu32&\$bM'z";   // #M^;GJgu32&$bM'z
//   $system_ids = array();
//   $channel_ids = array();
//   $data = array();


//   $wp_request_headers = array(
//     'Authorization' => 'Basic ' . base64_encode( "$username:$password" )//"arboretum_api:#M^;GJgu32&\$bM'z" )
//   );

//   $systems_url = $url . 'systems/';
//   $response = wp_remote_get(
//     $systems_url,
//     array(
//       'headers'   => $wp_request_headers
//     )
//   );

//   echo 'url: ' . $systems_url . '<br><br>';

//   if( ! is_wp_error( $response ) ) {
//     echo 'no errors' . '<br><br>';
//     if ( is_array( $response )) {
//     //  $headers = $response['headers']; // array of http header lines
//       // $body    = json_decode(json_encode($response['body'])); // use the content
//       $body = json_decode($response['body'], true);

//       // var_dump(json_encode($response['body']));
//       // echo '<br><br>';
//       // var_dump($response['body']);
//       // echo '<br><br>';
//       // var_dump($body) . '<br><br>';
//       echo 'original response:';
//       echo '<pre>' . $response['body'] . '</pre>';
//       echo '<br><br>';

//   //     echo 'response is: ' . $response . '<br><br>';
//   // //    echo 'headers are: ' . $headers . '<br><br>';
//   //     echo 'body text is: ' . $body . '<br><br>';
//   //     echo 'is array.<br><br>';

//       foreach($body['systems'] as $system):
//         $system_ids[$system['system_name']] = $system['id'];
//       endforeach;

//       echo 'system ids:<br>';
//       echo '<pre>';
//       var_dump($system_ids);
//       echo '</pre>';
//       echo '<br><hr><br>';


//       foreach($system_ids as $system_name => $system_id):
//         $system_url = $systems_url . $system_id;
//         $response2 = wp_remote_get(
//           $system_url,
//           array(
//             'headers'   => $wp_request_headers
//           )
//         );

//         $body2 = json_decode($response2['body'], true);

//         echo '/systems/:id/ :<br>';
//         echo '<pre>';
//         var_dump($response2['body']);
//         echo '</pre>';
//         echo '<br><hr><br>';

//         foreach($body2['channels'] as $key => $channel):
//           $channel_id = $channel['channel_id'];
//           if (array_key_exists($system_name, $channel_ids)) {
//             array_push($channel_ids[$system_name], $channel_id);
//           } else {
//             $channel_ids[$system_name] = [$channel_id];
//           }
//         endforeach;

//         // Add channels stuff here

//       endforeach;

//       // echo 'channel ids:<br>';
//       // echo '<pre>';
//       // var_dump($channel_ids);
//       // echo '</pre>';
//       // echo '<br><hr><br>';


//       foreach($channel_ids as $system => $ids):
//         $array_total_value = 0;

//         foreach($ids as $channel_id):
//           echo "channel id = " . $channel_id . '<br><br>';
//           $channel_url = $url . 'channeldata/agg/?channel_id=' . $channel_id . '&start_time=2021-01-01T00:00:00&end_time=2021-05-12T00:00:00&interval=1day';

//           $response3 = wp_remote_get(
//             $channel_url,
//             array(
//               'headers'   => $wp_request_headers
//             )
//           );

//           $body3 = json_decode($response3['body'], true);


//            echo 'channel ' . $system . ' :<br>';
//            echo '<pre>';
//            var_dump($response3['body']);
//            echo '</pre>';
//            echo '<br><hr><br>';

//           foreach($body3['channeldata']['intervals'] as $interval):
//             foreach($interval['values'] as $value):
//               $array_total_value += $value['val'];
//             endforeach;
//           endforeach;
//         endforeach;

//         echo 'value for this system over the timeperiod: ' . $array_total_value . '<br><hr><br>';

//       //  write_solar_data($system_name, $data);
//       endforeach;


//     } else {
//       echo 'is not array.<br><br>';
//     }
//   } else {
//     echo 'there is errors.<br><br>';
//     if( is_wp_error($response)) {
//       echo $response->get_error_message();
//     }
//   }
// }


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
