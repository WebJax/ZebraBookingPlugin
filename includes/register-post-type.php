<?php
/**
 * Dette er oprettelse af cpt til hold
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

/*
|--------------------------------------------------------------------------
| DEFINE THE CUSTOM POST TYPE FOR YDELSER
|--------------------------------------------------------------------------
*/
 
/**
 * Setup Ydelse Custom Post Type
 *
 * @since 1.0
*/
    // Custom Post Type Labels
    $labels = array(
        'name' => esc_html__( 'Ydelser', 'rc_tc' ),
        'singular_name' => esc_html__( 'Ydelse', 'rc_tc' ),
        'add_new' => esc_html__( 'Tilføj Ny', 'rc_tc' ),
        'add_new_item' => esc_html__( 'Tilføj Ny Ydelse', 'rc_tc' ),
        'edit_item' => esc_html__( 'Editer Ydelse', 'rc_tc' ),
        'new_item' => esc_html__( 'Ny Ydelse', 'rc_tc' ),
        'view_item' => esc_html__( 'Vis Ydelser', 'rc_tc' ),
        'search_items' => esc_html__( 'Søg efter Ydelse', 'rc_tc' ),
        'not_found' => esc_html__( 'Ingen ydelse fundet', 'rc_tc' ),
        'not_found_in_trash' => esc_html__( 'Ingen ydelse fundet i skraldespand', 'rc_tc' ),
        'parent_item_colon' => ''
    );
 
    // Supports
    $supports = array( 'title', 'editor', 'thumbnail', 'custom-fields' );
 
    // Custom Post Type Supports
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array( 'slug' => 'ydelse', 'with_front' => true ),
				'capability_type' => 'post',
				'hierarchical' => false,
        'show_in_menu' => 'edit.php?post_type=ydelse',
        'supports' => $supports,
        'taxonomies' => array( 'ydelsegruppe' ),
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-id', // you can set your own icon here
        'featured_image' => 'Profil billede',
        'set_featured_image' => 'Indstil ydelse billede',
        'remove_featured_image' => 'Fjern ydelse billede',
        'use_featured_image' => 'Brug som ydelse billede',
				'register_meta_box_cb' 	=> 'ydelse_meta_box',
    );

register_post_type( 'ydelse' , $args );

/****************************
 *
 * Adding meta values to 'ydelse' endpoint.
 * Only wp backend can create, update or delete.
 *
 ****************************/

function get_ydelse_pris_and_varighed() {
  register_rest_field( 'ydelse', 'jw_salon_booking_ydelse_pris', array(
          'get_callback'  => 'rest_get_ydelse_field',
          'update_callback'   => null,
          'schema'            => null,
       )
  );
  register_rest_field( 'ydelse', 'jw_salon_booking_varighed', array(
          'get_callback'  => 'rest_get_ydelse_field',
          'update_callback'   => null,
          'schema'            => null,
       )
  );
}
add_action( 'rest_api_init', 'get_ydelse_pris_and_varighed' );

function rest_get_ydelse_field( $post, $field_name, $request ) {
    return get_post_meta( $post[ 'id' ], $field_name, true );
}


// Save custom fields when post is saved
add_action('save_post', 'save_ydelse_meta', 1, 2); 


// Custom fields meta box
function ydelse_meta_box() {
	add_meta_box("ydelse_oplysninger", "Info om ydelsen", "ydelse_meta_options", "ydelse", "normal", "high" );
}

// Setting up input fields for custom fields (pris, varigehed)
function ydelse_meta_options() {
	global $post;
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="ydelsemeta_noncename" id="ydelsemeta_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	$jw_salon_booking_ydelse_pris = $post->jw_salon_booking_ydelse_pris;
	$jw_salon_booking_varighed = $post->jw_salon_booking_varighed;

	echo '<input type="text" value="'.$jw_salon_booking_ydelse_pris.'" placeholder="Indtast pris på ydelsen" id="jw_salon_booking_ydelse_pris" name="jw_salon_booking_ydelse_pris" style="width: 100%; padding: 2px 6px; font-size: 1.7em; height: 1.7em;"/>';
	echo '<p></p>';
  echo '<input type="text" value="'.$jw_salon_booking_varighed.'" placeholder="Indtast varighed på ydelsen (1:30)" id="jw_salon_booking_varighed" name="jw_salon_booking_varighed" style="width: 100%; padding: 2px 6px; font-size: 1.7em; height: 1.7em;"/>';
}

// Setting up for saving custom field meta data
function save_ydelse_meta($post_id, $post) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( isset($_POST['ydelsemeta_noncename']) && !wp_verify_nonce( $_POST['ydelsemeta_noncename'], plugin_basename(__FILE__) )) {
		return $post->ID;
	}	
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	if (isset($_POST['jw_salon_booking_ydelse_pris'])) $ydelse_meta['jw_salon_booking_ydelse_pris'] = $_POST['jw_salon_booking_ydelse_pris'];
	if (isset($_POST['jw_salon_booking_varighed'])) $ydelse_meta['jw_salon_booking_varighed'] = $_POST['jw_salon_booking_varighed'];
	
	// Add values of $ydelse_meta as custom fields
	if (isset($ydelse_meta)) {
		foreach ($ydelse_meta as $key => $value) { // Cycle through the $ydelse_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}	
}

// Setup list view to show title(standard) but changing headline, pris, varighed, description(beskrivelse) og ydelsegruppe(Custom Taxonomy)
// Setup abillity to change listorder of ydelsegruppe(taxonomy) in list viewas well as title which is standard.

add_action("manage_ydelse_posts_custom_column",  "ydelse_custom_columns");
add_filter("manage_ydelse_posts_columns", "ydelse_edit_columns");
add_filter("manage_edit-ydelse_sortable_columns", "my_sortable_ydelse_column");

function ydelse_edit_columns($columns){
  $columns = array(
    "cb" => "<input type='checkbox' />",
    "title" => "Ydelse Titel",
    "pris" => "Pris",
		"varighed" => "Varighed",
    "description" => "Beskrivelse",
    "ydelsegruppe" => "Gruppe",
  );
 
  return $columns;
}

function ydelse_custom_columns($column){
  global $post;
 
  switch ($column) {
    case "pris":
      echo $post->jw_salon_booking_ydelse_pris;
      break;
		case "varighed":
			echo $post->jw_salon_booking_varighed;
			break;
    case "description":
      the_excerpt();
      break;
    case "ydelsegruppe":
      echo get_the_term_list($post->ID, 'ydelsegruppe', '', ', ','');
      break;
  }
}

function my_sortable_ydelse_column( $columns ) {
    $columns['ydelsegruppe'] = 'ydelsegruppe';
 
    //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
 
    return $columns;
}