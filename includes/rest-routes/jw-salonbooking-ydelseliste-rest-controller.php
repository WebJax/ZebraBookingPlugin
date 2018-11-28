<?php
/**
 * Simple setup of getting 'ydelseliste' only with
 *  - Titel
 *  - Pris
 *  - Varighed
 *  - Beskrivelse
 **/

/**
* Don't run this php without Wordpress
*
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class jw_salonbooking_ydelseliste_REST_Controller extends WP_REST_Controller { 
  
    public function register_routes() {
        $version = '1';
        $namespace = 'jw_salonbooking/v' . $version;
          
        $base = '/ydelseliste/';
        register_rest_route( $namespace, $base, array(
          'methods' => 'GET',
          'callback' => array ($this, 'my_awesome_func'),
        ) );
    }
  
  
    public function register_hook() {
      add_action ( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Grab latest post title by an author!
     *
     * @param array $data Options for the function.
     * @return string|null Post title for the latest,  * or null if none.
     */
    public function my_awesome_func( $data ) {

      $ydelseliste = array();
      $counter=0;
      
      $posts_args = array(
        'post_type' => 'ydelse',
      );

      $query = new WP_Query($posts_args);
      while ( $query->have_posts() ) { 
        global $post;
        $query->the_post();
        $ydelseliste[$counter]['titel']       = $post->post_title;
        $ydelseliste[$counter]['pris']        = $post->jw_salon_booking_ydelse_pris;
        $ydelseliste[$counter]['varighed']    = $post->jw_salon_booking_varighed;
        $ydelseliste[$counter]['beskrivelse'] = $post->post_content;       
        $counter++;
      }     
      
      return $query;
    }
}

//* Registrer ydelseliste REST controller 
$ydelseliste_route = new jw_salonbooking_ydelseliste_REST_Controller();
$ydelseliste_route->register_hook();
    