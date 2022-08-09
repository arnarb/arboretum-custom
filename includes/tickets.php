<?php
use Arboretum\Models\Event as Event;
use Arboretum\Models\Location as Location;
use Timber\User as User;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Adding custom columns to the admin section for Tickets
 */
function set_custom_ticket_columns($columns) {
    $date = $colunns['date'];
    unset($columns['date']);
  
    $columns['user'] = __('User', 'arboretum');
    $columns['registrant'] = __('Registrant', 'arboretum');
    $columns['event'] = __('Event', 'arboretum');
    $columns['location'] = __('Location', 'arboretum');
    $columns['time_registered'] = __('Time Registered', 'arboretum');
    $columns['time_attended'] = __('Time Attended', 'arboretum');
    $columns['canceled'] = __('Canceled', 'arboretum');
    $columns['date'] = __('Date', $date);
  
    return $columns;
  }
  add_filter('manage_ticket_posts_columns', 'set_custom_ticket_columns');
  
  /**
   * 
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
  
      case 'location':
        $locations = '';

        $location_ids = get_field('location', $post_id);
        $num = count($location_ids);
        $i = 0;

        foreach($location_ids as $location_id) {
          $location = new Location($location_id);
          $locations .= $location->title;
  
          if(++$i != $num) {
            $locations .= ', ';
          }
        }

        echo $locations;
        break;

      case 'time_registered':
        $time_registered = strtotime($custom_fields['time_registered'][0]);
        echo date("F j, Y g:i a", $time_registered);
        break;
  
      case 'time_attended':
        $time_attended = $custom_fields['time_attended'][0];
        if (isset($time_attended) && $time_attended != '') {
          $time_attended = strtotime($time_attended);
          echo date("F j, Y g:i a", $time_attended);
        } 
        break;
  
      case 'canceled':
        $time_canceled = $custom_fields['time_canceled'][0];
        if (isset($time_canceled) && $time_canceled != '') {
          $time_canceled = strtotime($time_canceled);
          echo 'Canceled on ' . date("F j, Y g:i a", $time_canceled);
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
    $columns['event'] = 'event';
    $columns['location'] = 'location';
    $columns['time_registered'] = 'time_registered';
    $columns['time_attended'] = 'time_attended';
    $columns['canceled'] = 'time_canceled';
  
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
    } else if('event' == $orderby) {
      $query->set('meta_key', 'event');
      $query->set('orderby', 'meta_value');
    } else if('location' == $orderby) {
      $query->set('meta_key', 'location');
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
      foreach($tickets as $ticket) {
        setup_postdata($ticket);
        $user_id = get_field('user', $ticket->ID);

        
        if ($user_id != GUEST_ID) {
          $user = new User($user_id);
    
          $name = $user->first_name . ' ' . $user->last_name;
        } else {
          $name = 'Guest';
        }
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
    // Location column
    $values = array();
    foreach($tickets as $ticket) {
      setup_postdata($ticket);
      $location_ids = get_field('location', $ticket->ID);
      foreach($location_ids as $location_id) {
        $location = new Location($location_id);
        $values[$location_id] = $location->title;
      }
      wp_reset_postdata();
    }
  ?>
    <select name="ticket_location_filter">
    <option value=""><?php _e('All locations', 'ticket'); ?></option>
  <?php
    $current_v = isset($_GET['ticket_location_filter'])? $_GET['ticket_location_filter']:'';
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
  
    // Event filter
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

    // Location filter
    if (is_admin() &&
      $pagenow=='edit.php' &&
      isset($_GET['ticket_location_filter']) &&
      $_GET['ticket_location_filter'] != ''
      && $query->is_main_query()
    ) {
      $query->query_vars['meta_query'][] = array(
        'key' => 'location',
        'value' => '"'.$_GET['ticket_location_filter'].'"',
        'compare' => 'LIKE'
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
   * 
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
      $sheet->setCellValue("D1", "User Name");
      $sheet->setCellValue("E1", "User Email");
      $sheet->setCellValue("F1", "City");
      $sheet->setCellValue("G1", "State");
      $sheet->setCellValue("H1", "Country");
      $sheet->setCellValue("I1", "Zip Code");
      $sheet->setCellValue("J1", "Event Title");
      $sheet->setCellValue("K1", "Start Date");
      $sheet->setCellValue("L1", "Selected Venue Location");
  
      // Add custom questions
      $custom_question_positions = array();
      $column_number = 12;  // Capital A (65) + 11 other predetermined columns for chr()
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
      $columns = array_merge(array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'), array_values($custom_question_positions));
  
      $num = 1;
      // Populate rows with submissions
      foreach($tickets as $ticket) {
        $user = get_user_by('ID', $ticket->user);
        $num ++;

        $sheet->setCellValue("A$num", $ticket->post_title);
        $sheet->setCellValue("B$num", $ticket->ID);
        $sheet->setCellValue("C$num", $ticket->time_registered);        
        $sheet->setCellValue("D$num", "$user->first_name $user->last_name");
        $sheet->setCellValue("E$num", $user->user_email);
        $sheet->setCellValue("F$num", $user->city);
        $sheet->setCellValue("G$num", $user->state);
        $sheet->setCellValue("H$num", $user->country);
        $sheet->setCellValue("I$num", $user->zip);
  
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
  
        $sheet->setCellValue("J$num", $titles);
        $sheet->setCellValue("K$num", $dates);
        $sheet->setCellValue("L$num", $locations);
  
        
        $get_post_custom = get_post_custom($ticket->ID); 
        foreach($get_post_custom as $name=>$value) {
          if (strpos($name, 'custom_questions_') === 0 && !str_contains($name, '_answer')) {
  
            $question_num = substr($name, 0, strlen($name) - 9);
            $answer_name = $question_num . '_answer';
            $answer = $get_post_custom[$answer_name][0];
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
  