<?php
/**
 * Adds custom columns to the admin section for Plant Bios
 */
function set_custom_plant_columns($columns) {
    $date = $colunns['date'];
    unset($columns['date']);

    $columns['introduction_date'] = __('Introduction Year', 'arboretum');
    $columns['date'] = __('Date', $date);

    return $columns;
}
add_filter('manage_plant_posts_columns', 'set_custom_plant_columns');


/**
 * Sets what each custom column displays
 */
function custom_plant_column($column, $post_id) {
    switch ($column) {

        // display a introduction date
        case 'introduction_date':
            echo get_field('introduction_date', $post_id);
            break;
    }
}
add_action('manage_plant_posts_custom_column' , 'custom_plant_column', 10, 2);


/**
 * 
 */
function set_custom_plant_sortable_columns( $columns ) {
    $columns['introduction_date'] = 'introduction_date';

    return $columns;
}
add_action('pre_get_posts', 'plant_orderby');


/**
 * 
 */
function plant_orderby($query) {
    if(!is_admin())
        return;

    $orderby = $query->get('orderby');

    if('introduction_date' == $orderby) {
        $query->set('meta_key', 'introduction_date');
        $query->set('orderby', 'meta_value');
    }
}
add_filter('manage_edit-plant_sortable_columns', 'set_custom_plant_sortable_columns');