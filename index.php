<?php
/*
Plugin Name: Arboretum Custom
Description: Custom functions for Arboretum website
Version: 0.1.3
Author: Arnold Arboretum
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define('ARBORETUM_CUSTOM', plugin_dir_path( __FILE__ ));
define('ARBORETUM_CUSTOM_URL', plugin_dir_url( __FILE__ ));
define('GUEST_ID', 68);  // Hard coding the guest ID
define('PUBLIC_PROGRAMS_EMAIL', 'publicprograms@arnarb.harvard.edu');

include_once ARBORETUM_CUSTOM . 'includes/art-shows.php';
include_once ARBORETUM_CUSTOM . 'includes/directors.php';
include_once ARBORETUM_CUSTOM . 'includes/events.php';
include_once ARBORETUM_CUSTOM . 'includes/expeditions.php';
include_once ARBORETUM_CUSTOM . 'includes/mec-books.php';
include_once ARBORETUM_CUSTOM . 'includes/plants.php';
// include_once ARBORETUM_CUSTOM . 'includes/researchers.php';
include_once ARBORETUM_CUSTOM . 'includes/utilities.php';