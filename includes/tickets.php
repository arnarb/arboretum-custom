<?php
use Arboretum\Repositories\EventRepository;
use Arboretum\Repositories\TicketRepository;

use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Arboretum\Models\Location as Location;
use Timber\User as User;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * Adds custom columns to the admin section for Tickets
 */
function set_custom_ticket_columns($columns) {
  $date = $columns['date'];
  unset($columns['date']);

  $columns['user'] = __('User', 'arboretum');
  $columns['registrant'] = __('Registrant', 'arboretum');
  $columns['event'] = __('Event', 'arboretum');
  $columns['type'] = __('Type', 'arboretum');
  $columns['event_date'] = __('Event Date', 'arboretum');
  $columns['source'] = __('Source', 'arboretum');
  $columns['time_registered'] = __('Time Registered', 'arboretum');
  $columns['time_attended'] = __('Time Attended', 'arboretum');
  $columns['canceled'] = __('Canceled', 'arboretum');
  $columns['in_advance'] = __('In Advance', 'arboretum');
  $columns['reminder_sent'] = __('Reminder Sent', 'arboretum');
  $columns['on_waitlist'] = __('Waitlist', 'arboretum');
  $columns['off_waitlist_confirmation_sent'] = __('Off Waitlist Confirmation Sent', 'arboretum');
  // $columns['date'] = __('Date', $date);

  return $columns;
}
add_filter('manage_ticket_posts_columns', 'set_custom_ticket_columns');

/**
 * Sets what each custom column displays
 */
function custom_ticket_column($column, $post_id) {
  $custom_fields = get_post_custom($post_id);

  switch ($column) {
    case 'user':
      $user_id = $custom_fields['user'][0];// get_field('user', $post_id);

      if ($user_id != GUEST_ID) {
        $user = new User($user_id);
        echo $user->first_name . ' ' . $user->last_name;
      } else {
        echo 'Guest';
      }
      break;

    case 'registrant':
      $first_name = $custom_fields['first_name'][0];
      $last_name = $custom_fields['last_name'][0];
      $email = $custom_fields['email'][0];

      echo $first_name . ' ' . $last_name . '<br/>' . $email;
      break;

    case 'event':
      $events = '';

      $event_ids = get_field('event', $post_id);
      $num = count($event_ids);
      $i = 0;

      foreach($event_ids as $event_id) {
        $event = new Event($event_id);
        $events .= '<a href="/wp-admin/edit.php?post_type=ticket&ticket_event_filter=' . $event->ID . '">' . $event->title . '</a>';

        if(++$i != $num) {
          $events .= ', ';
        }
      }

      echo $events;
      break;

    case 'event_date':
      $event_date = strtotime($custom_fields['event_date'][0]);
      echo date("M d Y g:i a, D", $event_date);
      break;

    case 'source':
      echo $custom_fields['source'][0];
      break;

    case 'type':
      $type = get_field('type', $post_id);
      echo $type;
      //echo $type['label'];
      break;

    case 'time_registered':
      $time_registered = strtotime($custom_fields['time_registered'][0]);
      echo date("M d Y g:i a, D", $time_registered);
      break;

    case 'time_attended':
      if (isset($custom_fields['time_attended'][0]) && $custom_fields['time_attended'][0] != '') {
        $time_attended = $custom_fields['time_attended'][0];
        $time_attended = strtotime($time_attended);
        echo date("M d Y g:i a, D", $time_attended);
      } 
      break;

    case 'canceled':
      if (isset($custom_fields['time_canceled'][0]) && $custom_fields['time_canceled'][0] != '') {
        $time_canceled = $custom_fields['time_canceled'][0];
        $time_canceled = strtotime($time_canceled);
        echo 'Canceled on ' . date("M d Y g:i a, D", $time_canceled);
      }
      break;

    case 'in_advance':
      echo (($custom_fields['added_to_advance'][0] === '1') || ($custom_fields['added_to_advance'][0] === 1)) ? '<span style="color: #00c037; font-weight: 600;">✓</span>' : '<span style="color: #ff4400; font-weight: 600;">☓</span>';
      // echo $custom_fields['added_to_advance'][0];
      break;

    case 'reminder_sent':
      echo (($custom_fields['reminder_email_sent'][0] === '1') || ($custom_fields['reminder_email_sent'][0] === 1))? '<span style="color: #00c037; font-weight: 600;">✓</span>' : '<span style="color: #ff4400; font-weight: 600;">☓</span>';
      // echo $custom_fields['reminder_email_sent'][0];
      break;

    case 'on_waitlist':
      echo (($custom_fields['on_waitlist'][0] === '1') || ($custom_fields['on_waitlist'][0] === 1))? '<span style="color: #00c037; font-weight: 600;">✓</span>' : '<span style="color: #ff4400; font-weight: 600;">☓</span>';
      // echo $custom_fields['on_waitlist'][0];
      break;

    case 'off_waitlist_confirmation_sent':
      if (isset($custom_fields['off_waitlist_confirmation_sent'][0]) && $custom_fields['off_waitlist_confirmation_sent'][0] != '') {
        $off_waitlist = $custom_fields['off_waitlist_confirmation_sent'][0];
        $off_waitlist = strtotime($off_waitlist);
        echo 'Confirmation sent on: ' . date("M d Y g:i a, D", $off_waitlist);
      }
      break;
  }
}
add_action('manage_ticket_posts_custom_column', 'custom_ticket_column', 10, 2);
  

/**
 * Make custom columns sortable
 */
function set_custom_ticket_sortable_columns( $columns ) {
  $columns['user'] = 'user';
  $columns['type'] = 'type';
  $columns['event'] = 'event';
  $columns['event_date'] = 'event_date';
  $columns['time_registered'] = 'time_registered';
  $columns['time_attended'] = 'time_attended';
  $columns['canceled'] = 'time_canceled';
  $columns['on_waitlist'] = 'on_waitlist';

  return $columns;
}
add_filter('manage_edit-ticket_sortable_columns', 'set_custom_ticket_sortable_columns');


/**
 * Order tickets by filters
 */
function ticket_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('user' == $orderby) {
    $query->set('meta_key', 'user');
    $query->set('orderby', 'meta_value');
  } else if('type' == $orderby) {
    $query->set('meta_key', 'type');
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
  } else if('on_waitlist' == $orderby) {
    $query->set('meta_key', 'on_waitlist');
    $query->set('orderby', 'meta_value');
  }
}
add_action('pre_get_posts', 'ticket_orderby');


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
  foreach ($tickets as $ticket) {
    setup_postdata($ticket);
    $user = get_field('user', $ticket->ID);
    //$user = new User($user_id);

    if (gettype($user) === 'string') {
      $user = new User($user);
    }

    if ($user->ID != GUEST_ID) {
      $name = $user->first_name . ' ' . $user->last_name;
    } else {
        $name = 'Guest'; // : first name, last name they entered
    }
    $values[$user->ID] = $name;
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

  // Type column
  $values = array();
  foreach ($tickets as $ticket) {
    setup_postdata($ticket);
    $type = get_field('type', $ticket->ID);
    $values[$type] = $type;
    // $values[$type['value']] = $type['label'];
    //foreach ($types as $type) {
    //  $location = new Location($location_id);
    //  $values[$type] = $type;
    //}
    wp_reset_postdata();
  }
  ?>
    <select name="ticket_type_filter">
    <option value=""><?php _e('All types', 'ticket'); ?></option>
  <?php
  $current_v = isset($_GET['ticket_type_filter'])? $_GET['ticket_type_filter']:'';
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
    foreach ($tickets as $ticket) {
      setup_postdata($ticket);
      $event_ids = get_field('event', $ticket->ID);
      foreach ($event_ids as $event_id) {
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

  // Waitlist column
  $values = array();
  $values['1'] = 'On Waitlist';
  $values['0'] = 'Not on Waitlist';
  ?>
    <select name="ticket_waitlist_filter">
    <option value=""><?php _e('All', 'ticket'); ?></option>
  <?php
    $current_v = isset($_GET['ticket_waitlist_filter'])? $_GET['ticket_waitlist_filter']:'';
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
add_action('restrict_manage_posts', 'ticket_filters_restrict_manage_posts');


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

    // Type filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['ticket_type_filter']) &&
    $_GET['ticket_type_filter'] != '' && 
    $query->is_main_query()
  ) {
    $query->query_vars['meta_query'][] = array(
      'key' => 'type',
      'value' => $_GET['ticket_type_filter'],
      'compare' => '='
    );
  }

  // Event filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['ticket_event_filter']) &&
    $_GET['ticket_event_filter'] != '' && 
    $query->is_main_query()
  ) {
    $query->query_vars['meta_query'][] = array(
      'key' => 'event',
      'value' => '"'.$_GET['ticket_event_filter'].'"',
      'compare' => 'LIKE'
    );
  }

  // Waitlist filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['ticket_waitlist_filter']) &&
    $_GET['ticket_waitlist_filter'] != ''
    && $query->is_main_query()
  ) {
    $query->query_vars['meta_query'][] = array(
      'key' => 'on_waitlist',
      'value' => $_GET['ticket_waitlist_filter'],
      'compare' => '=='
    );
  }
}
add_filter('parse_query', 'ticket_filters');



/**
 * Adds the download option in bulk actions
 */
function register_generate_spreadsheet_bulk_action($bulk_actions) {
  $bulk_actions['download_tickets'] = __('Download Tickets', 'download_tickets');
  return $bulk_actions;
}
add_filter('bulk_actions-edit-ticket', 'register_generate_spreadsheet_bulk_action');


/**
 * Generate the spreadsheet for tickets
 */
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

    $ticket_num = count($tickets);

    // Set static column titles
    $sheet->setCellValue("A1", "Ticket");
    $sheet->setCellValue("B1", "Ticket Number");
    $sheet->setCellValue("C1", "Time Registered");
    $sheet->setCellValue("D1", "User");
    $sheet->setCellValue("E1", "Participant Name");
    $sheet->setCellValue("F1", "User Email");
    // $sheet->setCellValue("G1", "City");
    // $sheet->setCellValue("H1", "State");
    // $sheet->setCellValue("I1", "Country");
    // $sheet->setCellValue("J1", "Zip Code");
    // $sheet->setCellValue("K1", "Event Title");
    // $sheet->setCellValue("L1", "Start Date");
    // $sheet->setCellValue("M1", "Selected Venue Location");
    $sheet->setCellValue("G1", "Event Date");
    $sheet->setCellValue("H1", "Event Title");
    $sheet->setCellValue("I1", "Type");
    $sheet->setCellValue("J1", "Location");
    $sheet->setCellValue("K1", "Time Registered");
    $sheet->setCellValue("L1", "Time Attended");
    $sheet->setCellValue("M1", "Time Canceled");
    $sheet->setCellValue("N1", "Source of Interest");

    // Add custom questions
    $custom_question_positions = array();
    $column_number = 15;  // Capital A (65) + 14 other predetermined columns for chr()
    foreach($tickets as $ticket) {      

      $get_post_custom = get_post_custom($ticket->ID); 
      foreach($get_post_custom as $name=>$value) {
        if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {
          foreach($value as $value_name=>$question) {

            // See if it already contains this answer?
            if (!array_key_exists($question, $custom_question_positions)) {
              $column_letter = chr(65 + ($column_number % 26));
              $column = $column_letter . '1';
              $sheet->setCellValue($column, $question);
              $custom_question_positions[$question] = $column_letter;

              $column_number ++;
            }
          }
        }
      }
    }

    // Combine custom questions onto the column array
    $columns = array_merge(array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'), array_values($custom_question_positions));

    $num = 1;
    // Populate rows with submissions
    foreach($tickets as $ticket) {
      $user = new User($ticket->user);//get_user_by('ID', $ticket->user);
      $num ++;

      if ($ticket->user != GUEST_ID) {
        $name = $ticket->first_name . ' ' . $ticket->last_name;
        $user_name = $user->name;
      } else {
        $name = $user->first_name . ' ' . $user->last_name;
        $user_name = 'Guest';
      }

      $sheet->setCellValue("A$num", $ticket->post_title);
      $sheet->setCellValue("B$num", $ticket->ID);
      $sheet->setCellValue("C$num", $ticket->time_registered);    
      $sheet->setCellValue("D$num", "$user_name");
      $sheet->setCellValue("E$num", $name);
      $sheet->setCellValue("F$num", $ticket->email);
      $sheet->setCellValue("G$num", $ticket->event_date);
      // $sheet->setCellValue("G$num", $user->city);
      // $sheet->setCellValue("H$num", $user->state);
      // $sheet->setCellValue("I$num", $user->country);
      // $sheet->setCellValue("J$num", $user->zip);
      // $sheet->setCellValue("H$num", $ticket->type);

      // Consolidate event data into one string for entry into spreadsheet
      $n = 0;
      $event_count = count($ticket->event);
      $location_count = count($ticket->location);
      $titles = '';
      $dates = '';
      $locations = '';

      foreach($ticket->event as $event_id) {
        $n ++;
        $event = new Event($event_id);
        $titles .= $event->title;
        // TODO: improve date functionality
        $dates .= $event->start_date;

        if($n < $event_count) {
          $titles .= '; ';
          $dates .= '; ';
        }
      }

      $m = 0;
      foreach($ticket->location as $location_id) {
        $m ++;
        $location = new Location($location_id);
        $locations .= $location->title;

        if($m < $location_count) {
          $locations .= '; ';
        }
      }

      $sheet->setCellValue("H$num", $titles);
      $sheet->setCellValue("I$num", $event->type);
      $sheet->setCellValue("J$num", $locations);

      $sheet->setCellValue("K$num", $event->time_registered);
      $sheet->setCellValue("L$num", $event->time_attended);
      $sheet->setCellValue("M$num", $event->time_canceled);
      $sheet->setCellValue("N$num", $event->source);
      
      $get_post_custom = get_post_custom($ticket->ID); 
      foreach($get_post_custom as $name=>$value) {
        if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {

          $question_num = substr($name, 0, strlen($name) - 9);
          $answer_name = $question_num . '_answer';
          $answer = $get_post_custom[$answer_name][0] . '<br>';
          foreach($value as $value_name=>$question) {
            $column_letter = $custom_question_positions[$question];
            $column = $column_letter . $num;

            $sheet->setCellValue($column, $answer);
          }
        }
      }
    }

    // Set column width and text-wrap
    foreach($columns as $column) {
      $sheet->getColumnDimension($column)->setAutoSize(true);
      // $sheet->getColumnDimension($column)->setWidth('50');
      
      $sheet->getStyle($column . '1:' . $column . '1')->getFont()->setBold(true);
      $sheet->getStyle($column . '1:' . $column . '1')->getAlignment()->setHorizontal('center');
      $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setWrapText(true); 
      $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setHorizontal('left'); 
      // $sheet->getStyle($column . '2:' . $column . ($ticket_num + 1))->getAlignment()->setIndent(1); 
    }

    // Write excel sheet to file
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"Event-Registrations-$date.xlsx\"");
    header("Cache-Control: max-age=0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");
    $writer->save("php://output");
  }

  return $redirect_url;
}
add_filter('handle_bulk_actions-edit-ticket', 'generate_spreadsheet_bulk_action', 10, 3);


/**
 * Create custom WP cron job for sending out email reminders
 */
function arboretum_ticket_send_reminder_email() {
  // Get Site Settings values
  $settings = get_fields('options');
  $body = $settings['reminder_email']['body'];

  $testingBody = 'STAGING Woohoo!  In Tickets:<hr><br>';
  $headers = array(
    "Content-Type: text/html; charset=UTF-8\r\n",
    'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
  );

  $eventRepo = new EventRepository();
  $events = $eventRepo->getEvents(-1)->get();
  
  $date_format = 'Y-m-d H:i';
  $current_date = date($date_format);

  $event_ids = array();

  // Find events with an instance tomorrow
  foreach($events as $event) {
    $reminder_buffer = $event->get_field('reminder_buffer');
    if (!$reminder_buffer || $reminder_buffer === 0) {
      $reminder_buffer = $settings['reminder_email']['hours_prior'];
    }
    $venues = $event->get_field('venues');

    $testingBody .= '<strong>' . $event->title . ':</strong><br>';
    if ($venues > 0) {
      foreach ($venues as $venue) {
        $testingBody .= 'Venue: ' . $venue['location'][0]->post_title . '<br>';
        if ($venue['event_dates']) { // Has an assortment of dates
          $testingBody .= 'Array of dates:<br>';

          foreach ($venue['event_dates'] as $event_date) {
            $date = date($date_format, strtotime($event_date['date']));
            $testingBody .= 'CurrentDate: ' . $current_date . '<br>Date: ' . $date . '<br>';
            $difference = abs(round((strtotime($date) - strtotime($current_date)) / 3600, 1));
            $botestingBodydy .= 'Difference: ' . $difference . '<br><br>';            

            if ($difference <= $reminder_buffer) {
              $testingBody .= 'FOUND AN EVENT TO QUERY ' . $event->ID;
              array_push($event_ids, $event->ID);
            }
          }

        } elseif ($venue['end_date']) { // Has a date range from start_date to end_date
          $begin = new DateTime($venue['start_date']);
          $end = new DateTime($venue['end_date']);

          $interval = DateInterval::createFromDateString('1 day');
          $period = new DatePeriod($begin, $interval, $end);

          foreach ($period as $date) {
            $testingBody .= 'CurrentDate: ' . $current_date . '<br>Date: ' . $date->format($date_format) . '<br>';
            $difference = abs(round((strtotime($date->format($date_format) ) - strtotime($current_date)) / 3600, 1));
            $testingBody .= 'Difference: ' . $difference . '<br><br>';

            if ($difference <= $reminder_buffer) {
              $testingBody .= 'FOUND AN EVENT TO QUERY ' . $event->ID;
              array_push($event_ids, $event->ID);
            }
          }
        } else { // Just has a start_date
          if ($venue['start_date']) {
            $date = date($date_format, strtotime($venue['start_date']));

            $testingBody .= 'CurrentDate: ' . $current_date . '<br>Date: ' . $date . '<br>';
            $difference = abs(round((strtotime($date) - strtotime($current_date)) / 3600, 1));
            $testingBody .= 'Difference: ' . $difference . '<br><br>';

            if ($difference <= $reminder_buffer) {
              $testingBody .= 'FOUND AN EVENT TO QUERY ' . $event->ID;
              array_push($event_ids, $event->ID);
            }
          }
        }
        $testingBody .= '<br>';
      }
    }
    $testingBody .= '<hr><br>';
  }

  $testingBody .= 'Events to check for tickets: ' . implode(',  ', $event_ids) . '<br>';
  $testingBody .= 'Ticket IDs: ' . '<br>';

  if (!empty($event_ids)) {
    $ticketRepo = new TicketRepository();
    
    foreach ($event_ids as $event_id) {
      $tickets = $ticketRepo->getEventTickets($event_id)->get();
      foreach ($tickets as $ticket) {
        $ticket_id = $ticket->ID;
        $testingBody .= $ticket_id . '<br>';
  
        $date = $ticket->get_field('event_date');
        $testingBody .= 'Date: ' . $date . '<br>';
        $reminder_sent = $ticket->get_field('reminder_email_sent');
        $testingBody .= 'Reminder Sent: ' . $reminder_sent . '<br>';
      
        $difference = abs(round((strtotime($date) - strtotime($current_date)) / 3600, 1));
        if ($difference <= $reminder_buffer && $reminder_sent != 1){
          $testingBody .= 'Send a reminder email to ' . $ticket_id . ' and mark it as sent<br><br>';
              
          update_field('reminder_email_sent', 1, $ticket_id);
        } else {
          $testingBody .= "Don't send a reminder email to " . $ticket_id . " as it is already marked<br><br>";
        }
      }
    }
  }

  $testingBody .= '<hr><br><br>' . $body;

  /**
   * 
   * You have an event coming up! This is a reminder that you have registered for {program title} scheduled for {date} at {time}. Please meet at {location}. For directions, see below. 
   *
   *If you have any questions, please email us at publicprograms@arnarb.harvard.edu or call us at (617) 384-5209. If you can no longer attend this program, click here to cancel your registration. 
   *
   *Thank you!  
   *
   *{directions} 
   */
  
  // Send these to the proper recipients and a list of everything that was sent to pubic programs?
  $to                 = 'matt.caulkins@gmail.com';
  $subject            = 'test WP Cron hook for every 1min';
  
  wp_mail($to, $subject, $testingBody, $headers);
}
add_action('arboretum_ticket_reminder_email', 'arboretum_ticket_send_reminder_email');

if (!wp_next_scheduled('arboretum_ticket_reminder_email')) {
  wp_schedule_event(time(), 'every_minute', 'arboretum_ticket_reminder_email');
}



/**
 * Register event scripts
 */
// function ticket_scripts_enqueuer() {
  
//   wp_register_script('ticket-cancelation', ARBORETUM_CUSTOM_URL . 'js/ticket-cancelation.js', array('jquery'));
//   wp_localize_script('ticket-cancelation', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
//   wp_enqueue_script('jquery');
//   wp_enqueue_script('ticket-cancelation');
// }
// add_action('wp_enqueue_scripts', 'ticket_scripts_enqueuer');

/**
 * Cancel this ticket and if necessary take tickets off the waitlist
 */
function arboretum_ticket_cancelation() {
  // if(!wp_verify_nonce($_POST['nonce'], "cancel_ticket_nonce_" . $_POST['ticket_id'])) {
  //   exit ("No naughty business" . $_POST['nonce'] . ' ticket id: ' . $_POST['ticket_id']);
  // }

  $canceled = 1; // hardcoded for now, but will be all tickets per group

  $ticket_id = $_POST['ticket_id'];
  $ticket = new Ticket($ticket_id);
  $location_id = $ticket->location[0];
  $ticket_date = $ticket->event_date;
  $event_id = $ticket->event[0];

  $time_canceled = get_post_meta($ticket_id, "time_canceled", true);


  // $event = new Event($event_id);


  // $ticket_ids = array();
  // array_push($ticket_ids, $ticket_id);

  date_default_timezone_set('America/New_York');
  $date = date("Y-m-d H:i:s");
  $response = update_post_meta($ticket_id, 'time_canceled', $date);
  date_default_timezone_set('UTC');

  // $result['event_id'] = $event_id;
  // $result['ticket_id'] = $ticket_id;
  // $result['time_canceled'] = $date;
  // $result['location_id'] = $location_id;
  // $result['event_id'] = $event_id;
  // $result['ticket_date'] = $ticket_date;
  // if($response === false) {
  //   $result['type'] = "error";
  // }
  // else {
  //   $result['type'] = "success";
  // }

  // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  //   $result['if-else'] = 'HTTP_X_REQUESTED_WITH NOT EMPTY AND = xmlhttprequest';
  //   // $result = json_encode($result);
  //   // echo $result;
  // }
  // else {
  //   header("Location: ".$_SERVER["HTTP_REFERER"]);
  //   $result = json_encode($result);
  //   echo $result;
  // }

  // Switch this with an admin email?
  // $to                 = get_option('admin_email');
  // $subject            = 'Cancel ticket ' . $ticket_id;// Fired BY NONCE AJAX APPROACH';
  // $body               = $response . '    ' . $result;
  // wp_mail($to, $subject, $body, $headers);

    // Send a notice to waitlist that they are off waitlist
  // Get tickets for this Event

// FOR TESTING
  
  $ticket_repo = new TicketRepository();
  // $tickets = $ticket_repo->getEventTickets($event_id)->get();
  $tickets = $ticket_repo->getTicketsByVenueAndDate($event_id, $location_id, $ticket_date)->get();

  foreach($tickets as $ticket) {
    // $event_id = $ticket->event[0];
    // $event = new Event($event_id);

    // $test_body = 'Ticket : ' . $ticket->post_title . ': ' . $event->capacity . ' Event number: ' . $n . '<br>';
    // if (($n >= $event->capactiy) && ($n <= $canceled + $event->capacity) && ($ticket->on_waitlist == 1)) {
    //   $test_body .= '!! This ticket is off the waitlist !!<br><br> '. $ticket->user_email;
    if ($canceled > 0 && $ticket->on_waitlist == 1 && !isset($ticket->off_waitlist_confirmation_sent)) {
      arboretum_ticket_removed_from_waitlist($ticket);
      $canceled --;
    }
      // // update_post_meta($ticket->ID, 'off_waitlist_confirmation_sent', $date);
      // // update_post_meta($ticket->ID, 'on_waitlist', 0);

      // // Send an email to that person
      // $to                 = $ticket->email;
      // $subject            = 'Ticket cancelation stuffs';
      // $body               = is_set($event['getting_off_waitlist_email']) && is_set($event['getting_off_waitlist_email']['body']) ? $event['getting_off_waitlist_email']['body'] : $settings['getting_off_waitlist_email']['body'];
      // $tags               = array('[event]', '[date]');
      // $date               = date("F jS", strtotime($ticket_date));
      // $values             = array($event->title, $date);
      // $body               = str_replace($tags, $values, $body);
      // wp_mail($to, $subject, $body, $headers);
    //}
  }
  die();
}
add_action("wp_ajax_arboretum_ticket_cancelation", "arboretum_ticket_cancelation");
add_action("wp_ajax_nopriv_arboretum_ticket_cancelation", "arboretum_ticket_cancelation");

/**
 * Send a notification that they have been removed from the waitlist
 */
function arboretum_ticket_removed_from_waitlist($ticket) {
  $headers = array(
    "Content-Type: text/html; charset=UTF-8\r\n",
    'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
  );
  // Get Site Settings values
  $settings = get_fields('options');

  $ticket_id = $ticket->ID;
  // $location_id = $ticket->location[0];
  $ticket_date = $ticket->event_date;
  $event = new Event($ticket->event[0]);

  date_default_timezone_set('America/New_York');
  $date = date("Y-m-d H:i:s");

  update_post_meta($ticket_id, 'on_waitlist', 0);
  update_post_meta($ticket_id, 'off_waitlist_confirmation_sent', $date);

  $ticket = new Ticket($ticket_id);

  // For Each emails_and_messages -> type == waitlist_confirmation_email

  $to                 = $ticket->email;
  $subject            = 'You have been moved from the waitlist to registered for ' . $event->title;
  $body               = isset($event->getting_off_waitlist_email) && isset($event->getting_off_waitlist_email['body']) ? $event->getting_off_waitlist_email['body'] : $settings['getting_off_waitlist_email']['body'];
  $tags               = array('[event]', '[date]');
  $date               = date("F jS", strtotime($ticket_date));
  $values             = array($event->title, $date);
  $body               = str_replace($tags, $values, $body);
  wp_mail($to, $subject, $body, $headers);

  date_default_timezone_set('UTC');
}