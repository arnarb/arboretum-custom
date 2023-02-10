<?php
/*
Plugin Name: Arboretum Custom
Description: Custom functions for Arboretum website
Version: 0.1.2
Author: Arnold Arboretum
*/
use Arboretum\Repositories\DirectorRepository;

define( 'ARBORETUM_CUSTOM', plugin_dir_url( __FILE__ ) );

/**
 * Add a Members tab to the Memberpress navigation
 */
function mepr_add_user_tabs($user) {
?>
  <span class="mepr-nav-item members">
    <a href="/user-history/">History</a>
  </span>
  <span class="mepr-nav-item members">
    <a href="/members/">Members Area</a>
  </span>
<?php
}

add_action('mepr_account_nav', 'mepr_add_user_tabs');

/**
 * Finds if the member is active or inactive right now
 */
function get_mepr_status() {
  $mepr_options = MeprOptions::fetch();
  $mepr_user = MeprUtils::get_currentuserinfo();

  if ($mepr_user && $mepr_user->is_active()) {
      return true;
  }
  return false;
}

function is_protected_by_mepr_rule() {
  $mepr_options = MeprOptions::fetch();
  return MeprRule::is_locked(get_post());
}


/**
 * Sort events and art shows together by date
 */
function sort_shows_and_events($events) {
  $posts = array();

  foreach($events as $event) {
    if($event->start_date) {
      $event->event_date = date('Y-m-d H:i:s', strtotime($event->start_date)); // substr($event->start_date, 0, 4) . '-' . substr($event->start_date, 4, 2) . '-' . substr($event->start_date, 6) . ' 00:00:00'; // 23:59:59
    }
    if($event->end_date && !strpos($event->end_date, '-')) {
      $event->end_date = date('Y-m-d H:i:s', strtotime($event->end_date)); // substr($event->end_date, 0, 4) . '-' . substr($event->end_date, 4, 2) . '-' . substr($event->end_date, 6) . ' 00:00:00'; // 23:59:59
    }

    array_push($posts, $event);
  }

  usort($posts, function($a, $b)
  {
    if ($a->event_date == $b->event_date):
      return (0);
    endif;

    return (($a->event_date > $b->event_date) ? 1 : -1);
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
  $form_fields        =  $form_data['fields'];

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
  $headers = array(
    "Content-Type: text/html; charset=UTF-8\r\n",
    'From: The Arnold Arboretum <admin@arnarb.harvard.edu>' //'.get_option('admin_email').'>'
  );

  wp_mail($to, $subject, $body, $headers);
}


/**
 * Adding custom columns to the admin section for Art Shows
 */
add_filter('manage_art_show_posts_columns', 'set_custom_art_show_columns');

function set_custom_art_show_columns($columns) {
  $date = $colunns['date'];
  unset($columns['date']);

  $columns['start_date'] = __('Start Date', 'my-text-domain');
  $columns['end_date'] = __('End Date', 'my-text-domain');
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

add_filter( 'manage_edit-art_show_sortable_columns', 'set_custom_art_show_sortable_columns' );

function set_custom_art_show_sortable_columns( $columns ) {
  $columns['start_date'] = 'start_date';
  $columns['end_date'] = 'end_date';

  return $columns;
}

add_action('pre_get_posts', 'art_show_date_orderby');

function art_show_date_orderby($query) {
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

  $columns['start_year'] = __('Start Year', 'my-text-domain');
  $columns['end_year'] = __('End Year', 'my-text-domain');
  $columns['is_active'] = __('Is Active', 'my-text-domain');
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

add_filter( 'manage_edit-expedition_sortable_columns', 'set_custom_expedition_sortable_columns' );

function set_custom_expedition_sortable_columns( $columns ) {
  $columns['start_year'] = 'start_year';
  $columns['end_year'] = 'end_year';
  $columns['is_active'] = 'is_active';

  return $columns;
}

add_action('pre_get_posts', 'expedition_date_orderby');

function expedition_date_orderby($query) {
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

  $columns['introduction_date'] = __('Introduction Date', 'my-text-domain');
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

add_filter( 'manage_edit-plant_sortable_columns', 'set_custom_plant_sortable_columns' );

function set_custom_plant_sortable_columns( $columns ) {
  $columns['introduction_date'] = 'introduction_date';

  return $columns;
}

add_action('pre_get_posts', 'plant_date_orderby');

function plant_date_orderby($query) {
  if(!is_admin())
    return;

  $orderby = $query->get('orderby');

  if('introduction_date' == $orderby) {
    $query->set('meta_key', 'introduction_date');
    $query->set('orderby', 'meta_value');
  }
}


/**
 * Call to see if the language translantions dropdown should appear
 */

function check_for_translations() {
  global $post;
  $is_translated = apply_filters( 'wpml_element_has_translations', NULL, $post->ID, 'page');

  if(!$is_translated) {
    return 'hidden';
  }
}


/**
 * Extend AFC DatePicker field range
 */
function extend_afc_date_picker_range() {
  wp_enqueue_script( 'date-picker-js', ARBORETUM_CUSTOM . '/js/custom_date_picker.js', array(), '1.0.0', true );
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
