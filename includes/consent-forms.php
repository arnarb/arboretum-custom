<?php
use Arboretum\Models\Event as Event;
use Arboretum\Models\Ticket as Ticket;
use Timber\User as User;

/**
 * Adds custom columns to the admin section for Art Shows
 */
function set_custom_consent_form_columns($columns) {
    $date = $colunns['date'];
    unset($columns['date']);

    $columns['event'] = __('Event', 'arboretum');
    $columns['tickets'] = __('Tickets', 'arboretum');
    $columns['user'] = __('User', 'arboretum');
    $columns['participant_names'] = __('Participant Names', 'arboretum');

    $columns['date'] = __('Date', $date);

    return $columns;
}
add_filter('manage_consent_form_posts_columns', 'set_custom_consent_form_columns');


/**
 * Sets what each custom column displays
 */
function custom_consent_form_column($column, $post_id) {
    $custom_fields = get_post_custom($post_id);

    switch ($column) {
        case 'event':
            $events = '';
      
            $event_ids = get_field('event', $post_id);
            $num = empty($ticket_ids) ? 0 : count($event_ids);
            $i = 0;
      
            foreach($event_ids as $event_id) {
              $event = new Event($event_id);
              $events .= '<a href="/wp-admin/edit.php?s&post_type=ticket&ticket_event_filter=' . $event_id .'">' . $event->title . '</a>';
      
              if($i != $num) {
                $events .= ', ';
                $i++;
              }
            }
      
            echo $events;
            break;

        case 'tickets':
            $ticket_ids = get_field('tickets', $post_id);
            $num = empty($ticket_ids) ? 0 : count($ticket_ids);
            $i = 0;
            $tickets = '';

            foreach($ticket_ids as $ticket_id) {
              $ticket = new Ticket($ticket_id['ticket']);
              $tickets .= $ticket->post_title;
      
              if($i != $num) {
                $tickets .= '<br>';
                $i++;
              }
            }
        
            echo $tickets;
            break;

        case 'user':
            $user_id = $custom_fields['user'][0];// get_field('user', $post_id);
        
            if ($user_id != GUEST_ID) {
                $user = new User($user_id);
                echo $user->first_name . ' ' . $user->last_name;
            } else {
                echo 'Guest';
            }
            break;

        case 'participant_names':
          $participants = get_field('participants', $post_id);
          $num = empty($participants) ? 0 : count($participants);
          $i = 0;
          $participant_names = '';
          
          foreach($participants as $participant) {
            $participant_names .= $participant['participant_name'];

            if($i != $num) {
              $participant_names .= '<br>';
              $i++;
            }
          }

          echo $participant_names;
          break;
    }
}
add_action('manage_consent_form_posts_custom_column' , 'custom_consent_form_column', 10, 2);


/**
 * 
 */
function set_custom_consent_form_sortable_columns( $columns ) {
    $columns['event'] = 'event';
    $columns['user'] = 'user';

    return $columns;
}
add_filter('manage_edit-consent_form_sortable_columns', 'set_custom_consent_form_sortable_columns');


/**
 * 
 */
function consent_form_orderby($query) {
    if(!is_admin())
        return;

    $orderby = $query->get('orderby');

    if('event' == $orderby) {
        $query->set('meta_key', 'event');
        $query->set('orderby', 'meta_value');
    } else if('user' == $orderby) {
        $query->set('meta_key', 'user');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'consent_form_orderby');


////////////////////////////////
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
function consent_form_filters_restrict_manage_posts($post_type){
    global $wpdb, $table_prefix;

    $type = 'consent_form';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if('consent_form' !== $type) {
      return;
    }

    $consent_forms = get_posts(array('numberposts' => -1, 'post_type' => 'consent-form', 'posts_per_page' => -1));

    // User column
    $values = array();
    foreach ($consent_forms as $consent_form) {
      setup_postdata($consent_form);
      $user = get_field('user', $consent_form->ID);

      
      if ($user->ID != GUEST_ID) {
        $user = new User($user->ID);
  
        $name = $user->first_name . ' ' . $user->last_name;
      } else {
        $name = 'Guest'; // : first name, last name they entered
      }
      $values[$user->ID] = $name;
      wp_reset_postdata();
    }
  ?>
    <select name="consent_form_user_filter">
    <option value=""><?php _e('All users', 'consent_form'); ?></option>
  <?php
    $current_v = isset($_GET['consent_form_user_filter'])? $_GET['consent_form_user_filter']:'';
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
    foreach ($consent_forms as $consent_form) {
      setup_postdata($consent_form);
      $event_ids = get_field('event', $consent_form->ID);
      foreach ($event_ids as $event_id) {
        $event = new Event($event_id);
        $values[$event_id] = $event->title;
      }
      wp_reset_postdata();
    }
  ?>
    <select name="consent_form_event_filter">
    <option value=""><?php _e('All events', 'consent_form'); ?></option>
  <?php
    $current_v = isset($_GET['consent_form_event_filter'])? $_GET['consent_form_event_filter']:'';
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
add_action('restrict_manage_posts', 'consent_form_filters_restrict_manage_posts');


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
function consent_form_filters($query){
  global $pagenow;

  $type = 'consent_form';
  if (isset($_GET['post_type'])) {
    $type = $_GET['post_type'];
  }
  if('consent_form' !== $type) {
    return;
  }

  // User filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['consent_form_user_filter']) &&
    $_GET['consent_form_user_filter'] != '' &&
    $query->is_main_query()
  ) {
    $query->query_vars['meta_query'][] = array(
      'key' => 'user',
      'value' => $_GET['consent_form_user_filter'],
      'compare' => '='
    );
  }

  // Event filter
  if (is_admin() &&
    $pagenow=='edit.php' &&
    isset($_GET['consent_form_event_filter']) &&
    $_GET['consent_form_event_filter'] != ''
    && $query->is_main_query()
  ) {
    $query->query_vars['meta_query'][] = array(
      'key' => 'event',
      'value' => '"'.$_GET['consent_form_event_filter'].'"',
      'compare' => 'LIKE'
    );
  }
}
add_filter('parse_query', 'consent_form_filters');