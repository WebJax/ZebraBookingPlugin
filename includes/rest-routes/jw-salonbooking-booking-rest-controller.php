<?php
/**
* Don't run this php without Wordpress
*
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class jw_salonbooking_bookinger_REST_Controller extends WP_REST_Controller {
  
    public function register_routes() {
        $version = '1';
        $namespace = 'jw_salonbooking/v' . $version;
      
        // Registrer routes for de handlinger der skal kunne foretages mod entiteten jw_salon_booking_bookinger
        $base = 'bookinger';
        register_rest_route( $namespace, '/' . $base, array( // definer endpoint her: jw_salonbooking/v1/bookinger
          array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_items' ),
            'permission_callback' => array( $this, 'get_items_permissions_check' ),
            'args'                => array( $this->get_collection_params() ),
          ),
          array(
            'methods'         => WP_REST_Server::CREATABLE,
            'callback'        => array( $this, 'create_item' ),
            'permission_callback' => array( $this, 'create_item_permissions_check' ),
            'args'            => $this->get_endpoint_args_for_item_schema( true ),
          ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
          array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_item' ),
            'permission_callback' => array( $this, 'get_item_permissions_check' ),
            'args'            => array(
              'context'          => array(
                'default'      => 'view',
                'required'     => true,
              ),
              'params' => array(
                'required'     => false,
                'booking_id' => array(
                  'description'        => 'The id(s) for bookinger i søgningen.',
                  'type'               => 'integer',
                  'default'            => 1,
                  'sanitize_callback'  => 'absint',
                ),
              ),
              $this->get_collection_params()
            ),
          ),
          array(
            'methods'         => WP_REST_Server::EDITABLE,
            'callback'        => array( $this, 'update_item' ),
            'permission_callback' => array( $this, 'update_item_permissions_check' ),
            'args'            => $this->get_endpoint_args_for_item_schema( false ),
          ),
          array(
            'methods'  => WP_REST_Server::DELETABLE,
            'callback' => array( $this, 'delete_item' ),
            'permission_callback' => array( $this, 'delete_item_permissions_check' ),
            'args'     => array(
              'force'    => array(
                'default'      => false,
              ),
            ),
          ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/schema', array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_public_item_schema' ),
        ) );

    }
 
    /**
     * Checks if a given request has access to get items.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
      return current_user_can('read');
 
    }
 
    /**
     * Retrieves a collection of items.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {
        $params = $request->get_params();
        $items = jw_salonbooking_rest_get_bookings( $params ); //Find alle bookinger
        $data = array();
        foreach( $items as $item ) {
          $itemdata = $this->prepare_item_for_response( $item, $request );
          $data[] = $this->prepare_response_for_collection( $itemdata );
        }

        return new WP_REST_Response( $data, 200 );          
    }
 
    /**
     * Checks if a given request has access to get a specific item.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
      return current_user_can('read'); // Skal være kunde, der er det samme som en 'Subscriber' hvis eneste capability er 'Read'
 
    }
 
    /**
     * Retrieves one item from the collection.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_item( $request ) {
      		//get parameters from request
          $params = $request->get_params();
          $item = jw_salonbooking_rest_get_booking( $params[ 'id' ] );//Find en bestemt booking
          $data = $this->prepare_item_for_response( $item, $request );

          //return a response or error based on some conditional
          if ( 1 == 1 ) {
            return new WP_REST_Response( $data, 200 );
          }else{
            return new WP_Error( 'code', __( 'Bookingen kan ikke hentes', 'jw_salonbooking_textdomain' ) );
          }

    }
 
  
    /**
     * Checks if a given request has access to create items.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool True if the request has access to create items, WP_Error object otherwise.
     */
    public function create_item_permissions_check( $request ) {
      return current_user_can('read');
    }
 
    /**
     * Creates one item from the collection.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function create_item( $request ) {

      $item = $this->prepare_item_for_database( $request );

      if ( function_exists( 'sports_bench_rest_add_booking')  ) {
        $data = sports_bench_rest_add_booking( $item );
        if ( is_array( $data ) ) {
          return new WP_REST_Response( $data, 201 );
        } else {
          echo 'not created';
          return $data;
        }
      }

      return new WP_Error( 'cant-create', __( 'Bookingen kan ikke oprettes', 'jw_salonbooking_textdomain'), array( 'status' => 500 ) );

    }
   
    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool True if the request has access to update the item, WP_Error object otherwise.
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can('read');
    }
 
    /**
     * Updates one item from the collection.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
      public function update_item( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( function_exists( 'jw_salonbooking_rest_update_booking')  ) {
          $data = jw_salonbooking_rest_update_booking( $item );
          if ( is_array( $data ) ) {
            return new WP_REST_Response( $data, 200 );
          } else {
            return $data;
          }
        }

        return new WP_Error( 'cant-update', __( 'message', 'jw_salonbooking_textdomain'), array( 'status' => 500 ) );

      }
 
    /**
     * Checks if a given request has access to delete a specific item.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool True if the request has access to delete the item, WP_Error object otherwise.
     */
    public function delete_item_permissions_check( $request ) {
         return current_user_can('read');
    }
 
    /**
     * Deletes one item from the collection.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
      public function delete_item( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( function_exists( 'jw_salonbooking_rest_delete_team')  ) {
          $deleted = jw_salonbooking_rest_delete_team( $item );
          if (  $deleted == true  ) {
            return new WP_REST_Response( true, 200 );
          } else {
            return $deleted;
          }
        }

        return new WP_Error( 'cant-delete', __( 'Bookingen kan ikke slettes', 'jw_salonbooking_textdomain'), array( 'status' => 500 ) );
      }
 
    /**
     * Prepares one item for create or update operation.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|object The prepared item, or WP_Error object on failure.
     */
    protected function prepare_item_for_database( $request ) {
      		global $wpdb;
          $table_name = $wpdb->prefix . 'jw_salon_booking_bookinger';
      
          // Alle data til databasen skal renses for eventuelle koder der ikke hører til
          // wp_filter_nohtml_kses fjerner alle HTML koder
          // sanitize_text_field renser datafeltet for flere forskellige ting: https://developer.wordpress.org/reference/functions/sanitize_text_field/

          if ( isset( $request[ 'booking_id' ] ) ) { // Det fulde navn på et id
            $booking_id = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'booking_id' ] ) );
          } elseif ( isset( $request[ 'id' ] ) ) { // Det hurtige navn på et id
            $booking_id = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'id' ] ) );
          } else { // Hvis der ikke er sat noget id
            $booking_id = '';
          }

          if ( isset( $request[ 'booking_fra' ] ) ) {
            $booking_fra = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'booking_fra' ] ) );
          } else {
            $booking_fra = '';
          }
          
          if ( isset( $request[ 'booking_til' ] ) ) {
            $booking_til = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'booking_til' ] ) );
          } else {
            $booking_til = '';
          }
          
          if ( isset( $request[ 'booking_kundeid' ] ) ) {
            $booking_kundeid = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'booking_kundeid' ] ) );
          } else {
            $booking_kundeid = '';
          }
          
          if ( isset( $request[ 'booking_serviceid' ] ) ) {
            $booking_serviceid = wp_filter_nohtml_kses( sanitize_text_field( $request[ 'booking_serviceid' ] ) );
          } else {
            $booking_serviceid = '';
          }
      
          // dan et array til alle data og returner til der hvor metoden blev kaldt
      
          $item = array(
            'booking_id'        => $booking_id,
            'booking_fra'       => $booking_fra,
            'booking_til'       => $booking_til,
            'booking_kundeid'   => $booking_kundeid,
            'booking_serviceid' => $booking_serviceid,
          );
          
          return $item;
          
    }
 
    /**
     * Prepares the item for the REST response.
     *
     * @since 4.7.0
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function prepare_item_for_response( $item, $request ) {
      
      		$schema = $this->get_item_schema();
          $data   = array();
          $data = $item;
          $team = new Booking( (int)$item[ 'booking_id' ] );

          $data[ 'team_link' ] = $team->get_permalink();
          $data[ 'team_link' ] = str_replace( '&#038;', '&', $data[ 'team_link' ] );

          return $data;

    }
 
    /**
     * Prepares a response for insertion into a collection.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Response $response Response object.
     * @return array|mixed Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof WP_REST_Response ) ) {
            return $response;
        }
 
        $data   = (array) $response->get_data();
        $server = rest_get_server();
 
        if ( method_exists( $server, 'get_compact_response_links' ) ) {
            $links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
        } else {
            $links = call_user_func( array( $server, 'get_response_links' ), $response );
        }
 
        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }
 
        return $data;
    }
 
   
    /**
     * Retrieves the item's schema, conforming to JSON Schema.
     *
     * @since 4.7.0
     *
     * @return array Item schema data.
     */
    public function get_item_schema() {
        $schema = array(
          '$schema'    => 'http://json-schema.org/draft-04/schema#',
          'title'      => 'entry',
          'type'       => 'object',
          'properties' => array(
            'booking_id' => array(
              'description' => __( 'Id for bookingen.' ),
              'type'        => 'integer',
              'readonly'    => true,
            ),
          ),
        );
        return $schema;
    }
  
    /**
     * Retrieves the query params for the collections.
     *
     * @since 4.7.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_collection_params() {
        return array(
            'context'                 => $this->get_context_param(),
            'page'                    => array(
                'description'         => __( 'Current page of the collection.' ),
                'type'                => 'integer',
                'default'             => 1,
                'sanitize_callback'   => 'absint',
                'validate_callback'   => 'rest_validate_request_arg',
                'minimum'             => 1,
            ),
            'per_page'                => array(
                'description'         => __( 'Maximum number of items to be returned in result set.' ),
                'type'                => 'integer',
                'default'             => 10,
                'minimum'             => 1,
                'maximum'             => 100,
                'sanitize_callback'   => 'absint',
                'validate_callback'   => 'rest_validate_request_arg',
            ),
            'search'                  => array(
                'description'         => __( 'Limit results to those matching a string.' ),
                'type'                => 'string',
                'sanitize_callback'   => 'sanitize_text_field',
                'validate_callback'   => 'rest_validate_request_arg',
            ),
            'booking_id'              => array(
                'description'         => __( 'Id på den booking der skal ændres, slettes, hentes.' ), 
                'type'                => 'integer',
                'sanitize_callback'   => 'absint',
            ),
            'booking_fra'             => array(
                'description'         => __( 'Bookingens begyndelsestidspunkt. Dato og tid.' ),
                'type'                => 'string',
                'sanitize_callback'   => 'sanitize_text_field',
            ),
            'booking_fra'             => array(
                'description'         => __( 'Bookingens sluttidspunkt. Dato og tid.' ),
                'type'                => 'string',
                'sanitize_callback'   => 'sanitize_text_field',
            ),
            'booking_serviceid'       => array(
                'description'         => __( 'Id på den eller de services der booket' ), 
                'type'                => 'integer',
                'sanitize_callback'   => 'absint',
            ),
            'booking_kundeid'              => array(
                'description'         => __( 'Id på den kunde der har booket' ), 
                'type'                => 'integer',
                'sanitize_callback'   => 'absint',
            ),
        );
    }
 
}
/**
 * Takes the REST URL and returns an array of the results
 *
 * @param array $params
 *
 * @return array, array of the SQL results
 *
 * @since 1.1
 */
function jw_salonbooking_rest_get_bookings( $params ) {
    $response = '';

    if ( ( isset( $params[ 'booking_id' ] ) && $params[ 'booking_id' ] != null ) or ( isset( $params[ 'booking_fra' ] ) && $params[ 'booking_fra' ] != null ) or ( isset( $params[ 'booking_til' ] ) && $params[ 'booking_til' ] != null ) or ( isset( $params[ 'booking_serviceid' ] ) && $params[ 'booking_serviceid' ] != null ) or ( isset( $params[ 'booking_kundeid' ] ) && $params[ 'booking_kundeid' ] != null ) ) {
      $and = false;
      $search = '';
      if ( $params[ 'booking_id' ] != null ) {
        $search .= 'booking_id in (' . $params[ 'booking_id' ] . ')';
        $and = true;
      } if ( isset( $params[ 'booking_fra' ] ) && $params[ 'booking_fra' ] != null ) {
        if ( $and == true ) {
          $prefix = ' AND ';
        }  else {
          $prefix = '';
        }
        $search .= $prefix . 'booking_fra in ( "' . $params[ 'booking_fra' ] . '" )';
        $and = true;
      } if ( isset( $params[ 'booking_til' ] ) && $params[ 'booking_til' ] != null ) {
        if ( $and == true ) {
          $prefix = ' AND ';
        }  else {
          $prefix = '';
        }
        $search .= $prefix . 'booking_til in ( "' . $params[ 'booking_til' ] . '" )';
        $and = true;
      } if ( isset( $params[ 'booking_serviceid' ] ) && $params[ 'booking_serviceid' ] != null ) {
        if ( $and == true ) {
          $prefix = ' AND ';
        }  else {
          $prefix = '';
        }
        $search .= $prefix . 'booking_serviceid in ( "' . $params[ 'booking_serviceid' ] . '" )';
        $and = true;
      } if ( isset( $params[ 'booking_kundeid' ] ) && $params[ 'booking_kundeid' ] != null ) {
        if ( $and == true ) {
          $prefix = ' AND ';
        }  else {
          $prefix = '';
        }
      }

      global $wpdb;
      $table = $wpdb->prefix . 'jw_salon_booking_bookinger';
      $querystr = "SELECT * FROM $table WHERE $search;";
      $bookinger = $wpdb->get_results( $querystr );
      $booking_liste = [];

      foreach( $bookinger as $booking ) {
        $booking        = new Booking( (int) $booking->booking_id );
        $return_booking = array (
          'booking_id'          => $booking->booking_id,
          'booking_fra'         => $booking->booking_fra,
          'booking_til'         => $booking->booking_til,
          'booking_serviceid'   => $booking->booking_serviceid,
          'booking_kundeid'     => $booking->booking_kundeid,
        );

        array_push( $booking_list, $return_booking);
      }
      $response = $booking_list;

    } else {

      $bookinger     = jw_salonbookinger_get_bookinger();
      $booking_list = [];

      foreach ( $bookinger as $key => $label ) {
        $en_booking  = new EnBooking( (int) $key );
        $booking_info = array (
          'booking_id'          => $en_booking->booking_id,
          'booking_fra'         => $en_booking->booking_fra,
          'booking_til'         => $en_booking->booking_til,
          'booking_serviceid'   => $en_booking->booking_serviceid,
          'booking_kundeid'     => $en_booking->booking_kundeid,
        );
        array_push( $booking_list, $booking_info );
      }
      $response = $booking_list;

    }
    //print_r( $response );

    return $response;
}

/**
 * Returns an array of information for a booking
 *
 * @param int $booking_id
 *
 * @return array, information for a booking
 *
 * @since 1.4
 */
function jw_salonbooking_rest_get_booking( $booking_id ) {
    $the_booking  = new EnBooking( (int) $booking_id );
    $booking_info = array (
      'booking_id'          => $the_booking->booking_id,
      'booking_fra'         => $the_booking->booking_fra,
      'booking_til'         => $the_booking->booking_til,
      'booking_serviceid'   => $the_booking->booking_serviceid,
      'booking_kundeid'     => $the_booking->booking_kundeid,
    );

    return $booking_info;
}

function jw_salonbooking_rest_add_booking( $item ) {

  global $wpdb;
  $table_name = $wpdb->prefix . 'sb_teams';
  $team_name = $item[ 'team_name' ];
  $slug_test = $wpdb->get_results( "SELECT * FROM $table_name WHERE team_name LIKE $team_name" );

  if ( $slug_test == [] ) {
    $result = $wpdb->insert( $table_name, $item );
    echo $wpdb->last_error;
    if ( $result ) {
      return $item;
    } else {
      return new WP_Error( 'error_team_insert', __( 'There was an error creating the team. Please check your data and try again.', 'sports-bench' ), array( 'status' => 500 ) );
    }
  } else {
    return new WP_Error( 'error_team_insert', __( 'This team has already been created in the database. Maybe try updating the team.', 'sports-bench' ), array( 'status' => 500 ) );
  }

}

function jw_salonbooking_rest_update_booking( $item ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sb_teams';

	$team_id = $item[ 'team_id' ];
	$slug_test = $wpdb->get_results( "SELECT * FROM $table_name WHERE team_id = $team_id" );

	if ( is_array( $slug_test ) ) {
		$result = $wpdb->update( $table_name, $item, array ( 'team_id' => $item[ 'team_id' ] ) );
		if ( $result ) {
			return $item;
		} else {
			return new WP_Error( 'error_team_update', __( 'There was an error updating the team. Please check your data and try again.', 'sports-bench' ), array ( 'status' => 500 ) );
		}
	} else {
		return new WP_Error( 'error_team_update', __( 'This team does not exist. Try adding the team first.', 'sports-bench' ), array ( 'status' => 500 ) );
	}
}

function jw_salonbooking_rest_delete_team( $item ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sb_teams';
	$team_id = $item[ 'team_id' ];

	$slug_test = $wpdb->get_results( "SELECT * FROM $table_name WHERE team_id = $team_id" );

	if ( is_array( $slug_test ) ) {
		$result = $wpdb->delete( $table_name,
			array ( 'team_id' => $team_id ), array ( '%d' ) );
		if ( $result == false ) {
			return new WP_Error( 'error_team_delete', __( 'There was an error deleting the team. Please check your data and try again.', 'sports-bench' ), array ( 'status' => 500 ) );
		} else {
			return true;
		}
	} else {
		return new WP_Error( 'error_team_update', __( 'This team does not exist.', 'sports-bench' ), array ( 'status' => 500 ) );
	}

}

/**
 * Returns an array of information for a team
 *
 * @param int $team_id
 *
 * @return array, information for a team
 *
 * @since 1.4
 */
function jw_salonbookinger_get_bookinger( $booking_id ) {
	$en_booking  = new EnBookinger( (int) $booking_id );
	$booking_info = array (
      'booking_id'          => $en_booking->booking_id,
      'booking_fra'         => $en_booking->booking_fra,
      'booking_til'         => $en_booking->booking_til,
      'booking_serviceid'   => $en_booking->booking_serviceid,
      'booking_kundeid'     => $en_booking->booking_kundeid,
	);

	return $booking_info;
}


class EnBooking {
  public $booking_id;
  public $booking_fra;
  public $booking_til;
  public $booking_serviceid;
  public $booking_kundeid;
}
