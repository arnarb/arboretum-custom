<?php
/**
 * Adds custom columns to the admin section for Art Shows
 */
function set_custom_art_show_columns($columns) {
    $date = $colunns['date'];
    unset($columns['date']);

    $columns['start_date'] = __('Start Date', 'arboretum');
    $columns['end_date'] = __('End Date', 'arboretum');
    $columns['date'] = __('Date', $date);

    return $columns;
}
add_filter('manage_art_show_posts_columns', 'set_custom_art_show_columns');


/**
 * Sets what each custom column displays
 */
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
add_action('manage_art_show_posts_custom_column' , 'custom_art_show_column', 10, 2);


/**
 * 
 */
function set_custom_art_show_sortable_columns( $columns ) {
    $columns['start_date'] = 'start_date';
    $columns['end_date'] = 'end_date';

    return $columns;
}
add_filter('manage_edit-art_show_sortable_columns', 'set_custom_art_show_sortable_columns');


/**
 * 
 */
function art_show_orderby($query) {
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
add_action('pre_get_posts', 'art_show_orderby');