<?php
use Arboretum\Repositories\TicketRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;

/**
 * Add the unique ID to the event after saving or updating
 */
function event_save_post()
{
  $type = 'event';
  if (isset($_GET['post_type'])) {
    $type = $_GET['post_type'];
  }
  if('event' !== $type) {
    return;
  }

  $uniqueID = 0;
  // Loop through each location- time pair and add a unique ID

  $uniqueID++;
}
add_action( 'edit_post', 'event_save_post' );

/**
 * Sort the event dates into chronological order - THIS IS NOT YET WORKING
 */
// function sort_event_dates($post_id) {
//   $type = 'event';
//   if (isset($_GET['post_type'])) {
//     $type = $_GET['post_type'];
//   }
//   if('event' !== $type) {
//     return;
//   }

//   $post_status = get_post_status( $post_id );

//   switch ( $post_status ) {
//     case 'draft':
//     case 'auto-draft':
//     case 'pending':
//     case 'inherit':
//     case 'trash':
//       return;
//   }

//   // $event = new Event($post_id);
//   $venues = get_field('venues', $post_id);
//   $i = 0;
//   foreach ($venues as $venue) {
//     if ($venue['event_dates']) {
//       $dates = array();

//       foreach ($venue['event_dates'] as $event_date) {
//         array_push($dates, $event_date['date']);
//       }
//       usort($dates, function($time1, $time2) {
//         if (strtotime($time1) > strtotime($time2))
//           return 1;
//         else if (strtotime($time1) < strtotime($time2)) 
//           return -1;
        
//         return 0;
//       });

//       var_dump($dates);
//       for ($n = 0; count($dates); $n++) {
//         $field_name = 'venues_' . $i . '_event_dates_' . $n . '_date';
//         update_post_meta($post_id, $field_name, $dates[$n]);
//         // update_sub_field(array('venues', $i, 'event_dates', $n, 'date'), $dates[$n], $post_id);
//       }
//     }
//     $i ++;
//   }
// }
// add_action('post_updated','sort_event_dates');

// user-defined comparison function 
// based on timestamp
function compareByTimeStamp($time1, $time2)
{
   
}

/**
 * Adds custom columns to the admin section for Events
 */
function set_custom_event_columns($columns) {
  if ($columns) {
    $date = $colunns['date'];
    unset($columns['date']);

    $columns['venue'] = __('Venue', 'arboretum');
    $columns['type'] = __('Type', 'arboretum');
    $columns['event_date'] = __('Event Date', 'arboretum');
    $columns['registrations'] = __('Registrations', 'arboretum');
    $columns['date'] = __('Date', $date);

    return $columns;
  }
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
            $location = $venue['location'][0]->post_title;
            foreach ($venue['event_dates'] as $event_date) {
              $location .= '<br>';
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);

              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $location = $venue['location'][0]->post_title;
              
              foreach ($period as $date) {
                $location .= '<br>';
              }
            } else {
              $location = $venue['location'][0]->post_title . '<br>';              
            }
          }
          echo '<b>' . $location . '<b>';
          
          if ($count != $x) {
            echo '<hr>';
            $x ++;
          }
        }
      }
      break;

    case 'type':
      $venues = get_field('venues', $post_id);

      if ($venues > 0) {
        $x = 1;
        $count = count($venues);
        foreach ($venues as $venue) {
          if ($venue['event_dates']) {
            $type = $venue['type']['label'];
            foreach ($venue['event_dates'] as $event_date) {
              $type .= '<br>';
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);

              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $type = $venue['type']['label'];
              
              foreach ($period as $date) {
                $type .= '<br>';
              }
            } else {
              $type = $venue['type']['label'] . '<br>';              
            }
          }
          echo '<b>' . $type . '<b>';
          
          if ($count != $x) {
            echo '<hr>';
            $x ++;
          }
      }
    }
    break;

    case 'event_date':
      $ticketRepo = new TicketRepository();
      $venues = get_field('venues', $post_id);
      if ($venues > 0) {
        $x = 1;
        $count = count($venues);
        foreach ($venues as $venue) {
          $capacity = $venue['capacity'];

          if ($venue['event_dates']) {
            $eventTickets = $ticketRepo->getEventTickets($post_id)->get();
            $location_id = intval($venue['location'][0]->ID);

            foreach ($venue['event_dates'] as $event_date) {
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if (
                  $event_date['date'] == $ticket->event_date
                  && $location_id == $ticket->location[0] 
                  && $venue['type'] == $ticket->type
                ) {
                  $sold ++;
                }
              }

              $day = ($sold > $capacity) ? '<b style="color: #ff4400">' . date("M d Y g:i a, D", strtotime($event_date['date'])) . '</b><br>' : 
                (($sold > 0) ? '<b style="color: #2288ff">' . date("M d Y g:i a, D", strtotime($event_date['date'])) . '</b><br>' : 
                (date("M d Y g:i a, D", strtotime($event_date['date'])) . '<br>'));
              echo $day;
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);
              $end = $end->modify('+1 day');
              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $eventTickets = $ticketRepo->getEventTickets($post_id)->get();
              $location_id = intval($venue['location'][0]->ID);
              
              foreach ($period as $date) {
                $sold = 0;

                foreach ($eventTickets as $ticket) {
                  if (
                    $date->format('Y-m-d H:i:s') == $ticket->event_date
                    && $location_id == $ticket->location[0] 
                    && $venue['type'] == $ticket->type
                  ) {
                    $sold ++;
                  }
                }

                $day = ($sold > $capacity) ? '<b style="color: #ff4400">' . $date->format("M d Y g:i a, D") . '</b><br>' :
                  (($sold > 0) ? '<b style="color: #2288ff">' . $date->format("M d Y g:i a, D") . '</b><br>' :
                  ($date->format("M d Y g:i a, D") . '<br>'));
                echo $day;
              }
            } else {
              $eventTickets = $ticketRepo->getEventTickets($post_id)->get();
              $location_id = intval($venue['location'][0]->ID);
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if (
                  $venue['start_date'] == $ticket->event_date
                  && $location_id == $ticket->location[0] 
                  && $venue['type'] == $ticket->type
                ) {
                  $sold ++;
                }
              }

              $day = ($sold > $capacity) ? '<b style="color: #ff4400">' . date("M d Y g:i a, D", strtotime($venue['start_date'])) . '</b><br>' :
                (($sold > 0) ? '<b style="color: #2288ff">' . date("M d Y g:i a, D", strtotime($venue['start_date'])) . '</b><br>' :
                (date("M d Y g:i a, D", strtotime($venue['start_date'])) . '<br>'));
              echo $day;
            }              
          }

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
          $capacity = $venue['capacity'];
          $eventTickets = $ticketRepo->getEventTickets($post_id)->get();

          if ($venue['event_dates']) {
            $location_id = intval($venue['location'][0]->ID);

            foreach ($venue['event_dates'] as $event_date) {
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if (
                  $event_date['date'] == $ticket->event_date
                  && $location_id == $ticket->location[0] 
                  && $venue['type']['label'] == $ticket->type
                ) {
                  $sold ++;
                }
              }

              $total = ($sold > $capacity) ? '<b style="color: #ff4400">' . $sold . ' out of ' . $capacity . '</b><br>' : 
                (($sold > 0) ? ' <b style="color: #2288ff">' . $sold . ' out of ' . $capacity . '</b><br>' : 
                ($sold . ' out of ' . $capacity . '<br>'));
              echo $total;
            }
          } else {
            if ($venue['end_date']) {
              $begin = new DateTime($venue['start_date']);
              $end = new DateTime($venue['end_date']);
              $end = $end->modify('+1 day');
              $interval = DateInterval::createFromDateString('1 day');
              $period = new DatePeriod($begin, $interval, $end);

              $location_id = intval($venue['location'][0]->ID);

              foreach ($period as $date) {
                $sold = 0;

                foreach ($eventTickets as $ticket) {
                  if (
                    $date->format('Y-m-d H:i:s') == $ticket->event_date
                    && $location_id == $ticket->location[0] 
                    && $venue['type']['label'] == $ticket->type
                  ) {
                    $sold ++;
                  }
                }

                $total = ($sold > $capacity) ? '<b style="color: #ff4400">' . $sold . ' out of ' . $capacity . '</b><br>' :
                  (($sold > 0) ? ' <b style="color: #2288ff">' . $sold . ' out of ' . $capacity . '</b><br>' :
                  ($sold . ' out of ' . $capacity . '<br>'));
                echo $total;
              }
            } else {
              $location_id = intval($venue['location'][0]->ID);
              $sold = 0;

              foreach ($eventTickets as $ticket) {
                if (
                  $venue['start_date'] == $ticket->event_date
                  && $location_id == $ticket->location[0] 
                  && $venue['type']['label'] == $ticket->type
                ) {
                  $sold ++;
                }
              }

              $total = ($sold > $capacity) ? '<b style="color: #ff4400">' . $sold . ' out of ' . $capacity . '</b><br>' :
                (($sold > 0) ? ' <b style="color: #2288ff">' . $sold . ' out of ' . $capacity . '</b><br>' :
                ($sold . ' out of ' . $capacity . '<br>'));
              echo $total;
            }
          }

          if ($count != $x) {
            echo '<hr>';
            $x ++;
          };
        }
      }
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
    if (is_array($subjects) || is_object($subjects)) {
      foreach($subjects as $subject) {
        // $location = new Location($location_id);
        $values[$subject->slug] = $subject->name;
      }
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
// function event_scripts_enqueuer() {
//   wp_register_script('event-registration', ARBORETUM_CUSTOM_URL . 'js/event-registration.js', array('jquery'));
//   wp_localize_script('event-registration', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

//   wp_register_script('event-map', ARBORETUM_CUSTOM_URL . 'js/event-map.js', array('jquery'));
//   wp_localize_script('event-map', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

//   wp_enqueue_script('jquery');
//   wp_enqueue_script('event-registration');
//   wp_enqueue_script('event-map');
// }
// add_action('wp_enqueue_scripts', 'event_scripts_enqueuer');


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
  $current_date = date("Y-m-d H:i:s");
  date_default_timezone_set('UTC');

  $headers = array(
    "Content-Type: text/html; charset=UTF-8\r\n",
    'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
  );

  // Get Site Settings values
  $settings = get_fields('options');
  
  if (wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
    die;
  }
  $recipient = $_POST['email'];
  $requested = $_POST['requested'];

  if (isset($_POST['user']) && !empty($_POST['user'])) {
    $user_id = $_POST['user'];
    // $user = new User($user_id);
  } else {
    // GET GUEST USER - SET TO Public Programs
    $user_id = GUEST_ID;
    // $user = new User($user_id);
  }    

  $first_name = $_POST['firstName'];
  $last_name = $_POST['lastName'];
  
  // Get the Event
  $event_id = $_POST['event'];
  $event = new Event($event_id);

  // Get tickets for this Event
  $ticket_repo = new TicketRepository();
  $tickets = $ticket_repo->getEventTickets($event_id)->get();

  // Get the Venue
  // $location_id = $_POST['location'];
  // $location = new Location($location_id);


  $email_data = 'Event registration to ' . $event->title . ' for recipient: ' . $recipient . '. ';
  $email_data .= 'Number of tickets requested: ' . $requested;

     // "\nAvailability left: " . $_POST['availability'] . '  USER: ' . $user_id . 

  // Send notification of new registrant
  $to                 = PUBLIC_PROGRAMS_EMAIL;
  $subject            = 'New Registration for ' . $event->title;
  $body               = $email_data;

  wp_mail($to, $subject, $body, $headers);

  $extras = '';
  // Get the map, directions, and tickets sold per date
  $sold = array();
  $venues = get_field('venues', $event_id);
  foreach($venues as $venue) {
    $capacity = $venue['capacity'];
    $location_id = intval($venue['location'][0]->ID);
    $location = new Location($location_id); 

    if ($venue['location'] = $location) { // Only calculate for the proper location
      $directions = !empty($venue['directions']) ? $venue['directions'] : $location->directions;
      $map = $venue['map'] ? $venue['map'] : ($location->map ? $location->map : null);
    

      if ($venue['event_dates']) {
        foreach ($venue['event_dates'] as $event_date) {
          $date = new DateTime($event_date['date']);
          $date = $date->format('Y-m-d H:i:s');
          $sold[$date] = 0;

          foreach ($tickets as $ticket) {
            $ticket_date = new DateTime($ticket->event_date);
            $ticket_date = $ticket_date->format('Y-m-d H:i:s');

            if (
              $date === $ticket_date
              && $location_id === $ticket->location[0]
            ) {
              $sold[$date] ++;
            } else {
              $extras .= "Event Dates: something isn't adding up<br>";
            }

            $extras .= 'Event Date: ' . $date . ' Ticket Date: ' . $ticket_date . ' Location ID: ' . $location_id . ' Ticket Id: ' . $ticket->location[0] . '<br><br>';
          }
        }
      } else {
        if ($venue['end_date']) {
          $begin = new DateTime($venue['start_date']);
          $end = new DateTime($venue['end_date']);
          $interval = DateInterval::createFromDateString('1 day');
          $period = new DatePeriod($begin, $interval, $end);

          foreach ($period as $date) {
            $date = $date->format('Y-m-d H:i:s');
            $sold[$date] = 0;

            foreach ($tickets as $ticket) {
              $ticket_date = new DateTime($ticket->event_date);
              $ticket_date = $ticket_date->format('Y-m-d H:i:s');

              if (
                $date === $ticket_date
                && $location_id === $ticket->location[0]
              ) {
                $sold[$date] ++;
              } else {
                $extras .= "End Date: something isn't adding up<br>";
              }

              $extras .= 'Date: ' . $date . ' Ticket Date: ' . $ticket_date . 'Location ID: ' . $location_id . ' Ticket Id: ' . $ticket->location[0] . '<br><br>';
            }
          }
        } else {
          $start_date = new DateTime($venue['start_date']);
          $start_date = $start_date->format('Y-m-d H:i:s');
          $sold[$start_date] = 0;

          foreach ($tickets as $ticket) {
            $ticket_date = new DateTime($ticket->event_date);
            $ticket_date = $ticket_date->format('Y-m-d H:i:s');

            if (
              $start_date === $ticket_date
              && $location_id === $ticket->location[0]
            ) {
              $sold[$start_date] ++;
            } else {
              $extras .= "Start Date: something isn't adding up<br>";
            }

            $extras .= 'Start Date: ' . $start_date . ' Ticket Date: ' . $ticket_date . ' Location ID: ' . $location_id . ' Ticket Id: ' . $ticket->location[0] . '<br><br>';
          }
        }
      }
    }
  }

  
  $event_date = $_POST['date'];
  $end_time = $_POST['endTime'];
  $type = $_POST['type'];
  // $key = $_POST['key'];

  $total = $sold[$event_date];
  $location = new Location($location_id);
  $map_link = $map ? '<a href="https://www.google.com/maps/search/' . $map['lat'] . '+' . $map['lng'] . '">You can view a map here</a>' : 'no map'; // 42.299662200000007+-71.123806099999996

  ///// TODO: This should be where I need to edit the date logic
  // if (!empty($event->start_date)) {
  //   $event_date = date('Y-m-d H:i:s', $event->start_date);
  //   // $event_time = date('H:i', $event->start_date);
  // } else {
  //   $x = 0;
  //   while (date('Y-m-d H:i:s', intval($event->event_dates[$x])) < $date) {
  //     $x++;
  //   }
  //   $event_date = date('Y-m-d H:i:s', $event->event_dates[$x]);
  //   // $event_time = date('H:i', $event->event_dates[$x]);
  // }

  // Is it a waitlist confirmation?
  $waitlist = 0;
  if ($requested + $total > $capacity) {
    $waitlist = 1;
  }

  $response = '';
  $tickets = [];
  $hashs = [];

  // Create ticket(s)
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
          'event_date' => $event_date,
          'type' => $type, //array( 'value' => $key, 'label' => $type ),
          'location' => array(
            $location_id
          ),
          'time_registered' => $current_date,
          'on_waitlist' => $waitlist,
          'added_to_advance' => 0,
          'reminder_email_sent' => 0,
          'source' => $_POST['source']
        )
      )
    );
    
    $hash = hash('md2', $ticket_id . ': ' . $event->title . ' - ' . $first_name . ' ' . $last_name);

    // Get the post id in there so it's unique for each ticket
    $post_update = array(
      'ID'         => $ticket_id,
      // 'hash'       => $hash,
      'post_title' => $ticket_id . ': ' . $event->title . ' - ' . $first_name . ' ' . $last_name
    );
    wp_update_post($post_update);

    update_field('hash', $hash, $ticket_id);
    // $post_update = array(
    //   'ID'         => $ticket_id,
    // );
    // wp_update_post($post_update);

    $response .= $ticket_id . ', ';
    for ($j = 0; $j < $_POST['questions']; $j++) {
      // $question_num = 'question_' . $j;
      $question = $_POST['question' . $j]; // $question_num];
      // $answer_num = 'answer_' .$j;
      $answer = $_POST['answer' . $j]; // $answer_num];

      add_row('custom_questions', array(
        'question' => $question,
        'answer' => $answer
      ), $ticket_id);
    };

    array_push($tickets, $ticket_id); 
    array_push($hashs, $hash);
  }
    
  // Send confirmation email  
  $to                 = $recipient;
  $subject            = $waitlist === 1 ? 'Thank you for registering for the waitlist for '. $event->title : 'Confirmation to ' . $event->title;


  /**
   * [event] - event title
   * [date] - event date
   * [venue] - venue location and time
   * [cancelation_link] - link to cancel the ticket
   * [directions] - location directions
   * New Lines
   * Bold
   * Italics
   */
  $query = '?tickets=' . count($tickets);
  for ($n = 1; $n <= count($tickets); $n++) {
    $query .= '&id_' . $n . '=' . $tickets[$n-1] . '&q_' . $n . '=' . $hashs[$n-1];
  }

  $number = count($tickets) === 1 ? '1 participant' : count($tickets) . ' participants';

  // For Each emails_and_messages -> type == waitlist_confirmation_email

  $group              = $event->emails_and_messages;
  $cancel_link        = 'https://staging-arnoldarboretumwebsite.kinsta.cloud/events/cancel-event-registration/' . $query; //?id=' . $ticket_id . '&q=' . $hash;
  $body               = $waitlist === 1 ? ($group ? $group : $settings['waitlist_confirmation_email']['body']) : $settings['confirmation_email']['body'];
  $tags               = array('[requested]', '[event]', '[date]', '[venue]', '[cancelation_link]', '[directions]', '[map]'); // array('[event]', '[date]', '[time]', '[venue]', '[cancelation_link]', '[directions]', '[map]');
  $date               = date("F jS", strtotime($event_date));
  // $time               = date("g:ma",strtotime($event_date)) . ' - ' . $end_time;
  $values             = array($number, $event->title, $date, $location->post_title, $cancel_link, $directions, $map_link); // array($event->title, $date, $time, $location->post_title, $cancel_link, $directions, $map_link);
  $body               = str_replace($tags, $values, $body);

  
  // $extras .= 'Event Date: ' . $event_date . ' Capacity: ' . $capacity . ', Requested: ' . $requested . ', Total: ' . $total . ', Sold Tickets: <br>';

  // foreach($sold as $x => $y) {
  //   $extras .= 'Key: ' . $x . ' Value: ' . $y . '<br>';
  // }
  // $body .= $extras;
  
  wp_mail($to, $subject, $body, $headers);

  // Create consent form(s)
  
  // Get the registrant
  // $consent_name = $_POST['consentName'];
  // $consent_date = $_POST['consentDate'];

  // if ($_POST['guardianName']) {
  //   $guardian_name = $_POST['guardianName'];
  // }
  // if ($_POST['guardianDate']) {
  //   $guardian_date = $_POST['guardianDate'];
  // }

  $participant_text = $event->get_field('participant_text') ? $event->get_field('participant_text') : $settings['participant_text'];
  $guardian_text = $event->get_field('guardian_text') ? $event->get_field('guardian_text') : $settings['guardian_text'];


  $consent_form_id = wp_insert_post(
    array (
      'post_type' => 'consent_form',
      'post_title' => $event->title . ' - ' . $first_name . ' ' . $last_name,
      'post_status' => 'publish',
      'meta_input' => array(
        'event' => array(
          $event_id
        ),
        'user' => $user_id,
        'user_name' => $_POST['firstName'] . ' ' . $_POST['lastName'],
        // 'date' => $consent_date,
        'participant_text' => $participant_text,
        'guardian_text' => $guardian_text
        // 'guardian_name' => $guardian_name,
        // 'guardian_date' => $guardian_date,
      )
    )
  );

  foreach($tickets as $ticket_id) {
    add_row('tickets', array(
      'ticket' => $ticket_id,
    ), $consent_form_id);
  }

  // for ($n = 1; $n <= $_POST['participantNum']; $n++) {
  //   $participant_name = $_POST['participantName' . $n];
  //   $participant_date = $_POST['participantDate' . $n];
  //   add_row('participants', array(
  //     'participant_name' => $participant_name,
  //     'participant_date' => $participant_date
  //   ), $consent_form_id);
  // }

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

  //date_default_timezone_set('UTC');
  die;
}
add_action('wp_ajax_arboretum_event_registration', 'arboretum_event_registration_callback');
add_action('wp_ajax_nopriv_arboretum_event_registration', 'arboretum_event_registration_callback');