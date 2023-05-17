<?php 

/**
 * Allow for wildcards in WP Queries
 */
function allow_wildcards($where) {
    global $wpdb;
    $where = str_replace(
        "meta_key = 'tenure_$", 
        "meta_key LIKE 'tenure_%",
        $where
    );
    return $where;
}

add_filter('posts_where', 'allow_wildcards');

/**
 * Adds custom columns to the admin section for Events
 */
function set_custom_researcher_columns($columns) {
    if ($columns) {
        $date = $columns['date'];
        unset($columns['date']);
    
        $columns['fellowship'] = __('Roles', 'arboretum');
        $columns['award'] = __('Awards', 'arboretum');
        $columns['start_year'] = __('Start Year', 'arboretum');
        $columns['end_year'] = __('End Year', 'arboretum');
        $columns['date'] = __('Date', $date);
    
        return $columns;
    }
}
add_filter('manage_researcher_posts_columns', 'set_custom_researcher_columns');


  /**
 * Sets what each custom column displays
 */
function custom_researcher_column($column, $post_id) {
    date_default_timezone_set('America/New_York');

    switch ($column) {
        case 'fellowship':
            $tenures = get_field('tenure', $post_id);
            if ($tenures > 0) {
                $fellowships = '';
                foreach($tenures as $tenure) {
                    if ($tenure['fellowship']) {
                        $fellowships .= $tenure['fellowship'][0] . '<br>';
                    }
                }
                foreach($tenures as $tenure) {
                    if ($tenure['award']) {
                        $fellowships .= '<br>';
                    }
                }
            }

            echo $fellowships;
            break;

        case 'award':
            $tenures = get_field('tenure', $post_id);
            if ($tenures > 0) {
                $awards = '';
                foreach($tenures as $tenure) {
                    if ($tenure['fellowship']) {
                        $awards .= '<br>';
                    }
                }
                foreach($tenures as $tenure) {
                    if ($tenure['award']) {
                        $awards .= $tenure['award'][0] . '<br>';
                    }
                }
            }

            echo $awards;
            break;

        case 'start_year':
            $tenures = get_field('tenure', $post_id);
            if ($tenures > 0) {
                $start_dates = '';
                foreach($tenures as $tenure) {
                    if ($tenure['fellowship']) {
                        $start_dates .= $tenure['start_year'] . '<br>';
                    }
                }
                foreach($tenures as $tenure) {
                    if ($tenure['award']) {
                        $start_dates .= $tenure['start_year'] . '<br>';
                    }
                }
            }

            echo $start_dates;
            break;

        case 'end_year':
            $tenures = get_field('tenure', $post_id);
            if ($tenures > 0) {
                $end_dates = '';
                foreach($tenures as $tenure) {
                    if ($tenure['fellowship']) {
                        $end_dates .= $tenure['end_year'] . '<br>';
                    }
                }
                foreach($tenures as $tenure) {
                    if ($tenure['award']) {
                        $end_dates .= $tenure['end_year'] . '<br>';
                    }
                }
            }

            echo $end_dates;
            break;
    }
    
    date_default_timezone_set('UTC');
}
add_action('manage_researcher_posts_custom_column', 'custom_researcher_column', 10, 2);


/**
 * Set fields as sortable
 */
function set_custom_researcher_sortable_columns( $columns ) {
    $columns['fellowship'] = 'fellowship';
    $columns['award'] = 'award';
    $columns['start_year'] = 'start_year';
    $columns['end_year'] = 'end_year';
    return $columns;
}
add_filter('manage_edit-researcher_sortable_columns', 'set_custom_researcher_sortable_columns');


/**
 * Order fields
 */
function researcher_orderby($query) {
    if(!is_admin())
        return;

    if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'researcher' ) {
        $orderby = $query->get('orderby');

        if('fellowship' == $orderby) {
            $query->set('meta_key', 'tenure_$_fellowship');
        }

        $query->set('orderby', 'meta_value');

        return $query;
    }
}
add_action('pre_get_posts', 'researcher_orderby');


/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @return void
 */
function researcher_filters_restrict_manage_posts($post_type){
    global $wpdb, $table_prefix;
  
    $type = 'researcher';
    if (isset($_GET['post_type'])) {
      $type = $_GET['post_type'];
    }
    if('researcher' !== $type) {
      return;
    }
  
    $researchers = get_posts(array('numberposts' => -1, 'post_type' => 'researcher', 'posts_per_page' => -1));
  
    // Event Date column
    $values = array();
    foreach($researchers as $researcher) {
        setup_postdata($researcher);
        // $date = new DateTime(get_field('event_date', $event->ID));
        //     $values[$date->format('F Y')] = $date->format('Ym');
        // wp_reset_postdata();
    }
    arsort($values);
    ?>
        <select name="researcher_fellowship_filter">
        <option value=""><?php _e('Show all roles', 'researcher'); ?></option>
    <?php
        $current_v = isset($_GET['researcher_fellowship_filter'])? $_GET['researcher_fellowship_filter']:'';
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
add_action('restrict_manage_posts', 'researcher_filters_restrict_manage_posts');
  
  
/**
 * if submitted filter by post meta
 *
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function researcher_filters($query){
    global $pagenow;
  
    $type = 'researcher';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if('researcher' !== $type) {
        return;
    }
  
    // Subject Matter filter
    if (is_admin() &&
        $pagenow=='edit.php' &&
        isset($_GET['researcher_fellowship_filter']) &&
        $_GET['researcher_fellowship_filter'] != '' &&
        $query->is_main_query()
    ) {
        $date_year = substr($_GET['researcher_fellowship_filter'], 0, 4);
        $date_month = substr($_GET['researcher_fellowship_filter'], 4);
        // $raw_date = strtotime($date_year . '-' . $date_month . '-01');

        // $month_start = date('Y-m-d', $raw_date);
        // $month_end = date('Y-m-d', strtotime('+1 month', $raw_date));

        // $query->query_vars['meta_query'][] = array(
        //     'key'       => 'event_date',
        //     'value'     => array($month_start, $month_end),
        //     'compare'   => 'BETWEEN',
        //     'type'      => 'DATE'
        // );
    }
}
add_filter('parse_query', 'researcher_filters');