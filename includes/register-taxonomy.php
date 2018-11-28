<?php
/**
 * Dette er oprettelse af kategorier til ydelser
 *
 * @link       http://jaxweb.dk/
 * @since      1.0.0
 *
 * @package    salonbooking
 * @subpackage salonbooking/uninstall.php
 */ 

/**
 * Don't run this php without Wordpress
 *
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );




// opret en taxonomi for ydelse grupper
// Tilføj ny taxonomi, gør den hierarkisk (som 'categories')
    $labels = array(
        'name'              => _x( 'Ydelsegrupper', 'taxonomy general name', 'jw_salon_booking' ),
        'singular_name'     => _x( 'Ydelsegruppe', 'taxonomy singular name', 'jw_salon_booking' ),
        'search_items'      => __( 'Søg ydelsegrupper', 'jw_salon_booking' ),
        'all_items'         => __( 'Alle ydelsegrupper', 'jw_salon_booking' ),
        'parent_item'       => __( 'Forælder ydelsegruppe', 'jw_salon_booking' ),
        'parent_item_colon' => __( 'Forælder ydelsegruppe:', 'jw_salon_booking' ),
        'edit_item'         => __( 'Editer ydelsegruppe', 'jw_salon_booking' ),
        'update_item'       => __( 'Opdater ydelsegruppe', 'jw_salon_booking' ),
        'add_new_item'      => __( 'Tilføj ny ydelsegruppe', 'jw_salon_booking' ),
        'new_item_name'     => __( 'Nyt ydelsegruppe navn', 'jw_salon_booking' ),
        'menu_name'         => __( 'Ydelsegruppe', 'jw_salon_booking' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'show_in_rest'          => true,
        'rest_base'             => 'ydelsegruppe',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
        'rewrite'           => array( 'slug' => 'ydelsegruppe' ),
    );

register_taxonomy( 'ydelsegruppe', array( 'ydelse' ), $args );

add_action( 'rest_api_init', function () {
    register_rest_field( 'ydelsegruppe', 'showcase-taxonomy-image-id', array(
        'get_callback' => function( $term, $field_name, $request) {
            $post_id = get_term_meta( $term['id'], $field_name, true );
            $image_url = get_the_guid($post_id);
            return (string) $image_url;
        },
        'schema' => array(
            'description' => __( 'Billede til ydelsesgrupper.' ),
            'type'        => 'string'
        ),
    ) );
} );