<?php
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
 * Switch the email address for mail
 */
function correct_admin_email($email) {
    return "admin@arnarb.harvard.edu";
}
add_filter('wp_mail_from', 'correct_admin_email');