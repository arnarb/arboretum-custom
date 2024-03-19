<?php
use Arboretum\Models\Event;

require_once ARBORETUM_CUSTOM . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * Adds custom columns to the admin section for Events
 */
function set_custom_event_columns($columns) {
    if ($columns) {
        $date = $columns['date'];
        unset($columns['date']);
    
        //   $columns['venue'] = __('Venue', 'arboretum');
        //   $columns['type'] = __('Type', 'arboretum');
        $columns['event_date'] = __('Event Date', 'arboretum');
        $columns['signup_form'] = __('Signup Form', 'arboretum');
        //   $columns['registrations'] = __('Registrations', 'arboretum');
            
        $columns['submissions'] = __('Submissions', 'arboretum');
        // $columns['description'] = __('Description', $date);
        // $columns['date'] = __('Date', $date);
    
        return $columns;
    }
}
add_filter('manage_event_posts_columns', 'set_custom_event_columns');


  /**
 * Sets what each custom column displays
 */
function custom_event_column($column, $post_id) {
    date_default_timezone_set('America/New_York');

    switch ($column) {
        case 'event_date':
            if (get_field('event_date', $post_id)) {
                $date = new DateTime(get_field('event_date', $post_id));
                $date = $date->format('Y/m/d') . ' at ' . $date->format('h:i a');
                echo $date;
            }
            break;

        case 'signup_form':
            $form = get_field('signup_form', $post_id);
            if (is_array($form) && $form['id'] != 1) {
                echo '<a href="/wp-admin/admin.php?page=ninja-forms&form_id=' . $form['id'] . '">' . $form['data']['title'] . '</a>';
            }
            break;

        case 'submissions':
            $form = get_field('signup_form', $post_id);
            if (is_array($form) && $form['id'] != 1) {
                $subs = count(Ninja_Forms()->form($form['id'])->get_subs());
                echo '<a href="/wp-admin/admin.php?page=nf-submissions&form_id=' . $form['id'] . '">' . $subs . '</a>';
            }
            break;
    }
    
    date_default_timezone_set('UTC');
}
add_action('manage_event_posts_custom_column', 'custom_event_column', 10, 2);


/**
 * Set fields as sortable
 */
function set_custom_event_sortable_columns( $columns ) {
    $columns['event_date'] = 'event_date';

    return $columns;
}
add_filter('manage_edit-event_sortable_columns', 'set_custom_event_sortable_columns');


/**
 * Order fields
 */
function event_orderby($query) {
    if(!is_admin())
        return;

    $orderby = $query->get('orderby');

    if('event_date' == $orderby) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'event_orderby');


/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @return void
 */
function event_filters_restrict_manage_posts($post_type){
    global $wpdb, $table_prefix;
  
    $type = 'event';
    if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
    }
    if('event' !== $type) return;
  
    $events = get_posts(array('numberposts' => -1, 'post_type' => 'event', 'posts_per_page' => -1));
  
    // Event Date column
    $values = array();
    foreach($events as $event) {
        setup_postdata($event);
        $date = new DateTime(get_field('event_date', $event->ID));
            $values[$date->format('F Y')] = $date->format('Ym');
        wp_reset_postdata();
    }
    arsort($values);
    ?>
        <select name="event_date_filter">
        <option value=""><?php _e('Show all event dates', 'event'); ?></option>
    <?php
        $current_v = isset($_GET['event_date_filter'])? $_GET['event_date_filter']:'';
        foreach ($values as $label => $value) {
            printf
            (
                '<option value="%s"%s>%s</option>',
                $value,
                $value == $current_v? ' selected="selected"':'',
                $label
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
    if('event' !== $type)  return;
  
    // Subject Matter filter
    if (is_admin() &&
        $pagenow=='edit.php' &&
        isset($_GET['event_date_filter']) &&
        $_GET['event_date_filter'] != '' &&
        $query->is_main_query()
    ) {
        $date_year = substr($_GET['event_date_filter'], 0, 4);
        $date_month = substr($_GET['event_date_filter'], 4);
        $raw_date = strtotime($date_year . '-' . $date_month . '-01');

        $month_start = date('Y-m-d', $raw_date);
        $month_end = date('Y-m-d', strtotime('+1 month', $raw_date));

        $query->query_vars['meta_query'][] = array(
            'key'       => 'event_date',
            'value'     => array($month_start, $month_end),
            'compare'   => 'BETWEEN',
            'type'      => 'DATE'
        );
    }
}
add_filter('parse_query', 'event_filters');

// /**
//  * Add Quick Edit field for Description
//  */
// add_action( 'quick_edit_custom_box',  'event_quick_edit_custon_box');

// function event_quick_edit_custon_box($column_name, $post_type) {
//     switch ($column_name) {
//         case 'description':
//             ? >
//             <div class="inline-edit-col">
//                 <label>
//                     Test 
//                 </label>
//             </div>
//             <?php
//             break;
//     }
// }


// Generate a printout for the event
function generate_events_archive() { 
    $date = date("Y-m-d");
    $title = "Events Archive";

    $meta_query = array();

    $args = array(
        'numberposts'   => -1,
        'post_type'     => 'event',
    );

    // Setup spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($title);
    $sheet->setCellValue("A1", "Event Name");
    $sheet->setCellValue("B1", "Description");
    $sheet->setCellValue("C1", "Start Date");
    $sheet->setCellValue("D1", "End Date");
    $sheet->setCellValue("E1", "Total Registrations (if recorded)");
    $sheet->setCellValue("F1", "Max Registrants");
    $sheet->setCellValue("G1", "Image URL");

    // Get Event
    $events = get_posts($args);
    $num = 2;

    foreach($events as $event_raw) {
        $event = new Event($event_raw->ID);

        if ($event->image) {
            $url = $event->image['url'];
            $sheet->setCellValue("G$num", "$url");
        }
        $sheet->setCellValue("A$num", $event->title);

        $date = new DateTime($event->event_date);
        $date = $date->format('Y/m/d') . ' at ' . $date->format('h:i a');
        $sheet->setCellValue("C$num", $date);
        if ($event->end_date) {
            $date = new DateTime($event->end_date);
            $date = $date->format('Y/m/d') . ' at ' . $date->format('h:i a');
            $sheet->setCellValue("D$num", $date);
        }

        $form = get_field('signup_form', $event->ID);
        if (is_array($form) && $form['id'] != 1) {

            $subs = Ninja_Forms()->form($form['id'])->get_subs();
            $count = count($subs);
            $registrants = "Total Registrants: $count\n\n";

            // Get user data for registrations
            if ($count > 0) {
                foreach($subs as $sub) {
                    $field_values = $sub->get_field_values();
                    
                    $name = "Name: ";
                    $email = "Email: ";
                    $phone = "Phone: ";
                    $source = "Source: ";
                    
                    foreach($field_values as $key => $value) {
                        if (str_contains($key, "firstname")) {
                            $name .= $value;
                        } elseif (str_contains($key, "lastname")) {
                            $name .= $value;
                        } elseif (str_contains($key, "email")) {
                            $email .= $value;
                        } elseif (str_contains($key, "phone")) {
                            $phone .= $value;
                        } elseif (str_contains($key, "source")) {
                            $source .= $value;
                        }
                    }

                    $registrants .= "$name\n$email\n$phone\n$source\n\n";  
                }
            }

            $sheet->setCellValue("E$num", $registrants);
        }

        $num++;
    }
    $columns = array("A", "B", "C", "D", "E", "F", "G");

    // Set auto width and text wrap
    foreach ($columns as $column) {
        $sheet->getColumnDimension($column)->setWidth(50);
    }
    $sheet->getStyle("A2:G$num")->getAlignment()->setWrapText(true);
    $sheet->getStyle("A2:G$num")->getAlignment()->setVertical('top');

    // Write excel sheet to file
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"$title.xlsx\"");
    header("Cache-Control: max-age=0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");
    $writer->save("php://output");
    die;
}

// Add the export data button
function add_events_export_data_button($which)
{
    $type = 'event';
    if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
    }
    if('event' !== $type) return;
    

    if ($which == 'top') return;

    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]&export_data=true";
    $onclick = "location.href='$url'";

    echo '<hr><input type="button" value="Export Event Data" onclick="' . $onclick . '" class="button"/>';
}
add_action('manage_posts_extra_tablenav', 'add_events_export_data_button');

// Export data for selected event
function export_event_data()
{
    $type = 'event';
    if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
    }
    if('event' !== $type) return;

    if (isset($_GET['export_data'])) {
        generate_events_archive();
    }
}
add_action('wp', 'export_event_data');