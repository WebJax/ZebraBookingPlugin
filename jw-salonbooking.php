<?php
/*
Plugin Name: JaxWeb Salon Booking
Plugin URI: Jaxweb.dk
Description: Booking appointments for any beauty or hair salon
Version: 0.0.1
Author: Jacob Thygesen
Author URI: jaxweb.dk
License: GPLv2
*/

/**
 * Don't run this php without Wordpress
 *
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// include() or require() any necessary files here...
include_once( __DIR__ . '/includes/class-jw-salonbooking.php');

// Instantiate our class
$jw_salonbooking = jw_salonbooking::getInstance();
/* EOF */