<?php
/**
 * Adds custom columns to the admin section for Expeditions
 */
function set_custom_expedition_columns($columns) {
    $date = $colunns['date'];
    unset($columns['date']);

    $columns['start_year'] = __('Start Year', 'arboretum');
    $columns['end_year'] = __('End Year', 'arboretum');
    $columns['is_active'] = __('Is Active', 'arboretum');
    $columns['date'] = __('Date', $date);

    return $columns;
}
add_filter('manage_expedition_posts_columns', 'set_custom_expedition_columns');


/**
 * Sets what each custom column displays
 */
function custom_expeditions_column($column, $post_id) {
    $custom_fields = get_post_custom($post_id);

    switch ($column) {
        case 'start_year':
            echo get_field('start_year', $post_id);
            break;

        case 'end_year':
            echo get_field('end_year', $post_id);
            break;

        case 'is_active':
            echo (($custom_fields['is_active'][0] === '1') || ($custom_fields['is_active'][0] === 1))? '<span style="color: #00c037; font-weight: 600;">✓</span>' : '<span style="color: #ff4400; font-weight: 600;">☓</span>';
            break;
    }
}
add_action('manage_expedition_posts_custom_column' , 'custom_expeditions_column', 10, 2);


/**
 * Set fields as sortable
 */
function set_custom_expedition_sortable_columns( $columns ) {
    $columns['start_year'] = 'start_year';
    $columns['end_year'] = 'end_year';
    $columns['is_active'] = 'is_active';

    return $columns;
}
add_filter('manage_edit-expedition_sortable_columns', 'set_custom_expedition_sortable_columns');


/**
 * Filter fields
 */
function expedition_orderby($query) {
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
add_action('pre_get_posts', 'expedition_orderby');