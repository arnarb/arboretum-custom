<?php
use Arboretum\Repositories\TicketRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;


/**
 * Adds custom columns to the admin section for Events
 */
function set_custom_event_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['venue'] = __('Venue', 'arboretum');
  $columns['registrations'] = __('Registrations', 'arboretum');
  $columns['event_date'] = __('Event Date', 'arboretum');
  $columns['date'] = __('Date', $date);

  return $columns;
}
add_filter('manage_event_posts_columns', 'set_custom_event_columns');


/**
 * Sets what each custom column displays
 */
function custom_event_column($column, $post_id) {
  switch ($column) {
    case 'venue':
      $venues = get_field('venues', $post_id);

      if ($venues > 0) {
        $x = 1;
        $count = count($venues);
        foreach ($venues as $venue) {
          if ($venue['event_dates']) {
            $location_name = $venue['location'][0]->post_title;
            foreach ($venue['event_dates'] as $event_date) {
              $location_name .= '<br>';
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);

              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $location_name = $venue['location'][0]->post_title;
              
              foreach ($period as $date) {
                $location_name .= '<br>';
              }
            } else {
              $location_name = $venue['location'][0]->post_title . '<br>';              
            }
          }
          echo '<b>' . $location_name . '<b>';
          
          if ($count != $x) {
            echo '<hr>';
            $x ++;
          }
        }
      }
      break;

    case 'registrations':
      $ticketRepo = new TicketRepository();
      $venues = get_field('venues', $post_id);

      if ($venues > 0) {
        $x = 1;
        $count = count($venues);
        foreach ($venues as $venue) {
          if ($venue['event_dates']) {
            foreach ($venue['event_dates'] as $event_date) {
              $eventTickets = $ticketRepo->getEventTickets($post_id)->get();

              $location_id = $venue['location'][0]->ID;
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if ($location_id == $ticket->location[0]) {
                  $sold ++;
                }
              }

              $capacity = $venue['capacity'];
              echo $sold . ' out of ' . $capacity . '<br>';
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);

              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $location_name = $venue['location'][0]->post_title;
              
              foreach ($period as $date) {
                $eventTickets = $ticketRepo->getEventTickets($post_id)->get();

                $location_id = $venue['location'][0]->ID;
                $sold = 0;

                foreach ($eventTickets as $ticket) {
                  if ($location_id == $ticket->location[0]) {
                    $sold ++;
                  }
                }

                $capacity = $venue['capacity'];
                echo $sold . ' out of ' . $capacity . '<br>';
              }
            } else {
              $eventTickets = $ticketRepo->getEventTickets($post_id)->get();

              $location_id = $venue['location'][0]->ID;
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if ($location_id == $ticket->location[0]) {
                  $sold ++;
                }
              }

              $capacity = $venue['capacity'];
              echo $sold . ' out of ' . $capacity . '<br>';
            }
          }

          if ($count != $x) {
            echo '<hr>';
            $x ++;
          };

          // NEED THIS TO SORT OUT BY THE VENUE
          
        }
      }
      break;

      case 'event_date':
        // Need to expand this for which date / time was chosen
        
        $venues = get_field('venues', $post_id);
        if ($venues > 0) {
          $x = 1;
          $count = count($venues);
          foreach ($venues as $venue) {

            if ($venue['event_dates']) {
              foreach ($venue['event_dates'] as $event_date) {
                echo date("M d Y g:i a, l", strtotime($event_date['date'])) . '<br>';
              }
            } else {
              if ($venue['end_date']) {
                $begin = new DateTime($venue['start_date']);
                $end = new DateTime($venue['end_date']);

                $interval = DateInterval::createFromDateString('1 day');
                $period = new DatePeriod($begin, $interval, $end);
                
                foreach ($period as $date) {
                  echo $date->format("M d Y g:i a, l") . '<br>';
                }
              } else {
                echo date("M d Y g:i a, l", strtotime($venue['start_date'])) . '<br>';// strtotime($venue['start_date']) . '<br>';
              }              
            }

            if ($count != $x) {
              echo '<hr>';
              $x ++;
            }
          }
        }
        //  echo date("F j, Y g:i a", $event_date);
        break;
  }
}
add_action('manage_event_posts_custom_column', 'custom_event_column', 10, 2);


/**
 * Add filter dropdowns for tickets
 */
/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @author Ohad Raz
 *
 * @return void
 */
function event_filters_restrict_manage_posts($post_type){
  global $wpdb, $table_prefix;

  $type = 'event';
  if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
  }
  if('event' !== $type) {
    return;
  }

  $events = get_posts(array('numberposts' => -1, 'post_type' => 'event', 'posts_per_page' => -1));

  // User column
  $values = array();
  foreach($events as $event) {
    setup_postdata($event);
    $subjects = get_field('subject_matter', $event->ID);
    foreach($subjects as $subject) {
      // $location = new Location($location_id);
      $values[$subject->slug] = $subject->name;
    }
    wp_reset_postdata();
  }
  ?>
    <select name="event_subject_filter">
    <option value=""><?php _e('All Subject Matters', 'event'); ?></option>
  <?php
    $current_v = isset($_GET['event_subject_filter'])? $_GET['event_subject_filter']:'';
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
add_action('restrict_manage_posts', 'event_filters_restrict_manage_posts');


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
function event_filters($query){
  global $pagenow;

  $type = 'event';
  if (isset($_GET['post_type'])) {
    $type = $_GET['post_type'];
  }
  if('event' !== $type) {
    return;
  }

  // Subject Matter filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['event_subject_filter']) &&
    $_GET['event_subject_filter'] != '' &&
    $query->is_main_query()
  ) {
    $query->query_vars['tax_query'][] = array(
      'taxonomy' => 'subject',
      'field'    => 'slug',
      'terms'    => $_GET['event_subject_filter'],
    );
  }
}
add_filter('parse_query', 'event_filters');


/**
 * Register event scripts
 */
function event_scripts_enqueuer() {
  wp_register_script('event-registration', ARBORETUM_CUSTOM_URL . 'js/event-registration.js', array('jquery'));
  wp_localize_script('event-registration', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  
  wp_register_script('ticket-cancelation', ARBORETUM_CUSTOM_URL . 'js/ticket-cancelation.js', array('jquery'));
  wp_localize_script('ticket-cancelation', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

  wp_enqueue_script('jquery');
  wp_enqueue_script('event-registration');
  wp_enqueue_script('ticket-cancelation');
}
add_action('wp_enqueue_scripts', 'event_scripts_enqueuer');


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
  date_default_timezone_set('America/New_York');
  $date = date("Y-m-d H:i:s");

  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

  // Get Site Settings values
  $settings = get_fields('options');

  $recipient = $_POST['email'];
  $requested = $_POST['requested'];

  if (isset($_POST['user']) && !empty($_POST['user'])) {
    $user_id = $_POST['user'];
    // $user = new User($user_id);
  } else {
    // GET GUEST USER
    $user_id = 68;
    // $user = new User($user_id);
  }    

  $first_name = $_POST['firstName'];
  $last_name = $_POST['lastName'];
  
  // Get the Event
  $event_id = $_POST['event'];
  $event = new Event($event_id);

  // Get the Venue
  $location_id = $_POST['location'];
  $location = new Location($location_id);


  $email_data .= 'Number of tickets requested: ' . $requested;
  $email_data .= '   EVENT: ' . $event_id . '    RECIPIENT: ' . $recipient;   // "\nAvailability left: " . $_POST['availability'] . '  USER: ' . $user_id . 

  // Send notification of new registrant
  $to                 = 'matthew_caulkins@harvard.edu';
  $subject            = 'New Event Registration BY AJAX';
  $body               = $email_data;

  wp_mail($to, $subject, $body, $headers);

  if(!empty($event->start_date)) {
    $event_date = date('Y-m-d H:i:s', $event->start_date);
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
  // TODO: user the stuff from site settings
  $to                 = $recipient;
  $subject            = 'Confirmation to ' . $event->title;

  // if()

  // TODO: replace 
    // [event] - event title
    // [date] - event date
    // [venue] - venue location and time
  $body               = $settings['confirmation_email']['body'];

  $tags               = array('[event]', '[date]', '[venue]');
  $values             = array($event->title, $event_date, $location->post_title);
  $body               = str_replace($tags, $values, $body);
  // $body               = "Thank you for registering for " . $event->title . " on " . $event_date . " at " . $event_time . ". If you have any questions, please email us at <a href='publicprograms@arnarb.harvard.edu'>publicprograms@arnarb.harvard.edu</a> or call us at <a href='tel:617-384-5209'>(617) 384-5209</a>.";
  // $body               .= "<br><br>We welcome people of all abilities and are committed to facilitating a safe and engaging experience for all who visit. To request services such as an interpreter, wheelchair, or other assistance prior to attending an event, please contact us.";
  
  
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
        'post_title' => $event->title . ' - ' . $first_name . ' ' . $last_name,
        'post_status' => 'publish',
        'meta_input' => array(
          'user' => $user_id,
          'first_name' => $first_name,
          'last_name' => $last_name,
          'email' => $recipient,
          'event' => array(
            $event_id
          ),
          'location' => array(
            $location_id
          ),
          'time_registered' => $date,
        )
      )
    );

    $response .= $ticket_id . ', ';
    for ($j = 0; $j < $_POST['questions']; $j++) {
      // $question_num = 'question_' . $j;
      $question = $_POST['question_' . $j]; // $question_num];
      // $answer_num = 'answer_' .$j;
      $answer = $_POST['answer_' .$j]; // $answer_num];

      add_row('custom_questions', array(
        'question' => $question,
        'answer' => $answer
      ), $ticket_id);
    };
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
add_action('wp_ajax_arboretum_event_registration', 'arboretum_event_registration_callback');
add_action('wp_ajax_nopriv_arboretum_event_registration', 'arboretum_event_registration_callback');


/**
 * 
 */
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
add_action("wp_ajax_arboretum_ticket_cancelation", "arboretum_ticket_cancelation");