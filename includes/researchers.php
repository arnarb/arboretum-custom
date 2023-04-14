<?php 

/**
 * Allow for wildcards in WP Queries
 */
function allow_wildcards($where) {
    global $wpdb;
    $where = str_replace(
        "meta_key = 'tenure_%'", 
        "meta_key LIKE 'tenure_%'",
        $wpdb->remove_placeholder_escape($where)
    );
    return $where;
}

add_filter('posts_where', 'allow_wildcards');