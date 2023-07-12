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

    if ($post && $post->ID) {
        $is_translated = apply_filters('wpml_element_has_translations', NULL, $post->ID, 'page');
    }
    
    if(!$is_translated) {
        return 'hidden';
    }
}


/**
 * Extend Admin scripts
 */
function enqueue_admin_utility_scripts() {    
    // Date Picker
    wp_register_script('date-picker-js', ARBORETUM_CUSTOM_URL . 'js/custom-date-picker.js', array('jquery'));
    wp_localize_script('date-picker-js', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    wp_enqueue_script('date-picker-js', '', array(), false, true);
}
add_action('admin_enqueue_scripts', 'enqueue_admin_utility_scripts');
  
/**
 * Extend Visitor scripts
 */
function enqueue_utility_scripts() {    
    // Event Map
    wp_register_script('event-map', ARBORETUM_CUSTOM_URL . 'js/event-map.js', array('jquery'));
    wp_localize_script('event-map', 'arbAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    wp_enqueue_script('event-map');
}
add_action('wp_enqueue_scripts', 'enqueue_utility_scripts');


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

// Add custom focal point to image
function add_focal_point_to_attachment_fields_to_edit($form_fields, $post) {
    $imageHTML = '
        <script>
            if (typeof posX === "undefined") {
                const posX = document.querySelector("#acf-field_642ec6749754a");
                const posXReadout = document.querySelector("#acf-field_642ec6749754a-alt");
                posX.addEventListener("change", positionReticle);
                posXReadout.addEventListener("change", positionReticle);
            }
            if (typeof posY === "undefined") {
                const posY = document.querySelector("#acf-field_642ec7429754b");
                const posYReadout = document.querySelector("#acf-field_642ec7429754b-alt");
                posY.addEventListener("change", positionReticle);
                posYReadout.addEventListener("change", positionReticle);
            }

            function createReticle() {
                let reticle = document.querySelector("#reticle");
                if (!reticle) {
                    reticle = document.createElement("img");
                    reticle.src = "/wp-content/uploads/2023/04/reticle.png";
                    reticle.style = "position: absolute; width: 25px;"
                    reticle.id = "reticle";

                    container = document.querySelector(".js-focal-point-selector");
                    container.appendChild(reticle);
                }
                positionReticle();
            }

            function positionReticle() {
                const posX = document.querySelector("#acf-field_642ec6749754a");
                const posXReadout = document.querySelector("#acf-field_642ec6749754a-alt");
                const posY = document.querySelector("#acf-field_642ec7429754b");
                const posYReadout = document.querySelector("#acf-field_642ec7429754b-alt");

                const reticle = document.querySelector("#reticle");
                reticle.style = "position: absolute; width: 25px; left: calc(" + posX.value + "% - 13px); top: calc(" + posY.value + "% - 13px);";
            }

            function setPosPhoto(event) {
                const posX = document.querySelector("#acf-field_642ec6749754a");
                const posXReadout = document.querySelector("#acf-field_642ec6749754a-alt");
                const posY = document.querySelector("#acf-field_642ec7429754b");
                const posYReadout = document.querySelector("#acf-field_642ec7429754b-alt");
                
                const bounds = document.querySelector(".js-focal-point-selector").getBoundingClientRect();

                const percX = Math.floor((event.offsetX / bounds.width) * 100);
                const percY = Math.floor((event.offsetY / bounds.height) * 100);

                reticle.style = "position: absolute; width: 25px; left: calc(" + percX + "% - 13px); top: calc(" + percY + "% - 13px);";
                posX.value = percX;
                posXReadout.value = percX;
                posY.value = percY;
                posYReadout.value = percY;
            }

            setTimeout(createReticle, 300);
        </script>

        <div class="js-focal-point-selector" style="position: relative; width: fit-content; height: fit-content;" onmousedown="setPosPhoto(event)">' . wp_get_attachment_image($post->ID, 'medium') . '</div>
    
    ';
    $form_fields['focal'] = array(
        'label' => __( 'Focal Point' ),
        'helps' => __( 'Pick the center of focus for this image' ),
        'input' => 'html',
        'html' => $imageHTML
    );
    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'add_focal_point_to_attachment_fields_to_edit', 10, 2);

/**
 * Switch the email address for mail
 */
function correct_admin_email($email) {
    return "admin@arnarb.harvard.edu";
}
add_filter('wp_mail_from', 'correct_admin_email');