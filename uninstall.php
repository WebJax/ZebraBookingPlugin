<?php

/**
 * Dette er til brug for når du AFINSTALLERE pluginet, ikke når du deaktivere
 * Fjerner tabellen bookinger fra wp_db
 *
 * @link       http://jaxweb.dk/
 * @since      1.0.0
 *
 * @package    salonbooking
 * @subpackage salonbooking/uninstall.php
 */ 

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
$table_name = $wpdb->prefix . 'jw_salon_booking_bookinger';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
delete_option("jw_salon_booking_db_version");

//TODO: Remove customer if woo_commerce is not installed