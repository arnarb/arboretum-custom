<?php
/**
 * Edit login page
 */
function arb_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'arb_login_logo_url');
  

/**
 * 
 */
function arb_login_logo_url_title() {
    return 'Arnold Arboretum';
}
add_filter('login_headertext', 'arb_login_logo_url_title');
  

/**
 * Add login form shortcode
 */
function arb_login_shortcode() {
    $args = array(
        'echo'            => true,
        'label_username' => __( 'Email Address' ),
        // 'redirect'        => get_permalink( get_the_ID() ),
        'remember'        => true,
        'value_remember'  => true,
    );
  
    return '<div class="arb-form">' . wp_login_form( $args ) . '</div>';
}
add_shortcode( 'arb_login', 'arb_login_shortcode' );


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
        } else {  // Art Shows - use the end date to sort these
            // if ($event->start_date) {
            //     $event->start_date = date($format, strtotime($event->start_date)); // substr($event->start_date, 0, 4) . '-' . substr($event->start_date, 4, 2) . '-' . substr($event->start_date, 6) . ' 00:00:00'; // 23:59:59
            // }
            // if ($event->end_date) { //} && !strpos($event->end_date, '-')) {
                $event->start_date = date($format, strtotime($event->end_date)); // substr($event->end_date, 0, 4) . '-' . substr($event->end_date, 4, 2) . '-' . substr($event->end_date, 6) . ' 00:00:00'; // 23:59:59
            //     $event->end_date = date($format, strtotime($event->end_date));
            // }
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
 * 
 */
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
 * Extend AFC DatePicker field range
 */
function extend_afc_date_picker_range() {    
  wp_register_script('date-picker-js', ARBORETUM_CUSTOM_URL . 'js/custom-date-picker.js', array('jquery'));
  wp_localize_script('date-picker-js', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

  wp_enqueue_script('date-picker-js', '', array(), false, true);
  wp_enqueue_script('event-registration');
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