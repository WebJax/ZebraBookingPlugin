<?php
/**
* Don't run this php without Wordpress
*
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



class jw_salonbooking
{  
  /**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;
  
	/**
	 * This is our constructor
	 *
	 * @return void
	 */
  private function __construct() {
    // Tie into WordPress Hooks and any functions that should run on activation
    register_activation_hook( __FILE__, 'jw_salonbooking_activating' );
    
    // Translation
    add_action ('plugins_loaded', array( $this, 'jw_salonbooking_textdomain'));

    //Register Custom Post Type and Custom Taxonomy
    add_action( 'init', array ( $this, 'jw_salon_booking_registrer_cpt' ) );
        
    //Constructing custom endpoints and adding meta data to user rest endpoint
    add_action( 'rest_api_init', array ( $this, 'jw_salonbooking_register_endpoints') );
    add_action( 'rest_insert_user', array ( $this, 'add_meta_to_user_rest_api', 12, 3 ) );
    
    //Create customer-role if it isn't created by another plugin (ex. woocommerce)
    add_action( 'init', array ( $this, 'jw_salon_booking_create_customer_role' ) );
    
    //Back end scripts and styles
    add_action( 'admin_enqueue_scripts', array ( $this, 'load_jw_salon_booking_wp_admin_style_and_script' ) );
    add_action( 'admin_menu', array ($this, 'jw_salon_booking_menu') ); 

    //Front end scripts, styles and shortcodes
    add_action( 'wp_enqueue_scripts', array ($this, 'load_jw_salon_booking_style' ) );
    require_once (dirname( __FILE__ ) .'/shortcodes.php');
  }
  
  /**
  * Activating the plugin:
  *   - Create the bookingtable
  *   - Define versionvariable
  */
  public function jw_salonbooking_activating() {
      // Opret tabel til bookingerne
      global $wpdb;
      global $jw_salon_booking_db_version;

      $charset_collate = $wpdb->get_charset_collate();

      // Oprettelse af booking tabel når plugin aktiveres
      // kundeid = get_current_user_id() --- https://developer.wordpress.org/reference/functions/get_current_user_id/
      // ydelseid = Custom Post Type - service - ID in wp_posts 
      $table_name = $wpdb->prefix . 'jw_salon_booking_bookinger';
      $sql = "CREATE TABLE $table_name (
        id bigint(9) NOT NULL AUTO_INCREMENT,
        fra datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        til datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        kundeid bigint(9),
        ydelseid bigint(9),
        PRIMARY KEY  (id)
      ) $charset_collate;";


      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );

      add_option( 'jw_salon_booking_db_version', $jw_salon_booking_db_version );

      // Restart permalinks efter at post typen er blevet registeret
      flush_rewrite_rules();
  }
  
  function add_meta_to_user_rest_api($user, $request, $create) {
      if ($request['meta']) {
          $user_id = $user->ID;
          foreach ($request['meta'] as $key => $value) {
              update_user_meta( $user_id, $key, $value );
          }
      }
  }
  
  /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return WP_Comment_Notes
	 */
	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
  
  /**
   * Save booking 
   *
   * @return void
   */
  public function jw_salonbooking_savebooking() {
    $current_user = wp_get_current_user();		 
    $pass_validation = true; 
    if (!isset($_POST['tekst'])) {
      $itxt = "Der er ikke oplyst et telefonnummer";			
    } else {
      $itxt = $_POST['tekst'];
    }
    $data = array(
      'userid' => $current_user->ID,
      'bookingstart' => $_POST['starthidden'],
      'bookingend' => $_POST['sluthidden'],
      'infotext' => $itxt
    );
    global $wpdb;
    $table_name = $wpdb->prefix . 'trailerbookingcalendar';
    $wpdb->insert($table_name, $data, '%s'); 
  }

  /**
   * Register the custom rest routes for ydelseliste and the booking table
   *
   * @since 1.4
   * @return void
   */
  function jw_salonbooking_register_endpoints(){
    
    //ydelseliste
    require_once (dirname( __FILE__ ) . '/rest-routes/jw-salonbooking-ydelseliste-rest-controller.php');

    //* Registrer ydelseliste REST controller 

    $ydelseliste_route = new jw_salonbooking_ydelseliste_REST_Controller();
    $ydelseliste_route->register_routes();
    
   
    // Get class for booking rest controller
    require_once (dirname( __FILE__ ) . '/rest-routes/jw-salonbooking-booking-rest-controller.php');

    //* Registrer bookinger REST controller 
    $bookinger_route = new jw_salonbooking_bookinger_REST_Controller();
    $bookinger_route->register_routes();

  }  
  
  /**
   * Create customer role if it isnt created by woocommerce
   *
   * @return void
   */
  public function jw_salon_booking_create_customer_role() {
    global $wp_roles;  // Delcaring roles and collecting the author role capability here.
    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }
    
    $author = $wp_roles->get_role('subscriber');
    //Adding a customer role with subscriber capabilities if it doesnt exist
    if ( !get_role('customer')) {
      $wp_roles->add_role('customer', 'Kunde', $author->capabilities);
    }
  }

  /**
   * load admin menu
   *
   * @return void
   */
  public function jw_salon_booking_menu() { 
    add_menu_page('Vis kalender', 
                  'Salon Booking',
                  'publish_posts', // Giver alle med publish capabilities mulighed for at se menupunkterne heriblandt Trænere
                  'jw_salon_booking', 
                  array( $this, 'jw_salon_vis_booking_kalender' ), 
                  'dashicons-calendar-alt',
                  9);
    //add_submenu_page('jw_salon_booking', 'Hold', 'Hold', 'manage_options', 'edit.php?post_type=hold');
    add_submenu_page('jw_salon_booking', 'Booking', 'Opret ny Booking', 'publish_posts', '');
    add_submenu_page('jw_salon_booking', 'Ydelser', 'Se ydelser', 'publish_posts', 'edit.php?post_type=ydelse');
    add_submenu_page('jw_salon_booking', 'Ydelse', 'Opret ny ydelse', 'publish_posts', 'post-new.php?post_type=ydelse');    
    add_submenu_page('jw_salon_booking', 'Ydelsegrupper', 'Se ydelsegrupper', 'publish_posts', 'edit-tags.php?taxonomy=ydelsegruppe');    
    add_submenu_page('jw_salon_booking', 'Kunder', 'Se kunder', 'publish_posts', 'users.php');
    add_submenu_page('jw_salon_booking', 'Kunde', 'Opret ny kunde', 'publish_posts', 'user-new.php');  
  }

  /**
   * Show page for booking calendar
   *
   * @return void
   */
  public function jw_salon_vis_booking_kalender() {
		/* Call the form request check */
		if ( isset($_POST['user_info_nonce']) ) {
			$this->jw_salonbooking_savebooking();
		} ?>

    <div id="headline">
      <h2>Bookingkalender</h2>
    </div>
    <form action="">
      <?php wp_nonce_field('user_info', 'user_info_nonce', true, true) ?>
      <div id='vaelg-kunde-og-ydelse'>
        <div id="vaelg-kunde-container">
          <label><span>Vælg kunde: </span>
            <select id="vaelg-en-kunde">
              <?php
              $args = array (
                'role' => 'customer',
              );
              $users = get_users($args);
              if ($users) {
                foreach ($users as $user) {
                  echo '<option value="'.$user->ID.'">'.$user->display_name.'</option>';
                }            
              }
              ?>
            </select>
          </label>
        </div>  
        <div id="vaelg-ydelse-container">
          <label><span>Vælg ydelse: </span>
            <select id="vaelg-en-ydelse">
              <?php
                $this->hent_overgrupper();
              ?>  
            </select>
          </label>
        </div>
        Vælg herefter tid i kalenderen
      </div>
      <div id='calendar'></div>       
    </form>
  <?php
  }
  
  /**
   * Hent ydelsesliste med overgrupper og sorteret i undergrupper
   *
   * @return $void
   * ydelser inddelt i deres respektive overgrupper >> undergrupper
   */
  public function hent_overgrupper() {
    $parent_cat_args = array (
      'taxonomy' => 'ydelsegruppe',
      'parent' => 0,
      'hide_empty' => false,
    );
    $parent_cats = get_categories($parent_cat_args);

    if ($parent_cats) {
      foreach ($parent_cats as $parent_cat) {
        echo sprintf ('<option disabled>%1$s</option>', $parent_cat->name);
        $this->hent_ydelser_med_underkategorier($parent_cat);
      }
    }
  }
  
  /**
   * Hent ydelser sorteret efter underkategori/gruppe
   *
   * @return $items
   * ydelser inddelt i deres respektive underkategorier/gruppe
   */
  public function hent_ydelser_med_underkategorier($parent_cat) {
    $cat_args = array (
      'taxonomy' => 'ydelsegruppe',
      'parent' => $parent_cat->term_id,
      'number' => 0,
    );
    $ydelse_cats = get_categories($cat_args);
    foreach ($ydelse_cats as $ydelse_cat) {
      $args = array( 	
        'ydelsegruppe' => $ydelse_cat->slug, // Her skal custom taxonomy anvendes istedet for category
      );
      echo sprintf('<option disabled>&nbsp;%1$s</option>', $ydelse_cat->name);
      $query = new WP_Query($args);
      while ( $query->have_posts() ) { 
        global $post;
        $query->the_post();
        echo sprintf('<option class="behandling" value="%1$d" data-varighed="%4$s">&nbsp;&nbsp;%2$s - %3$d,00 kr. - %4$s</option>',
               $post->ID,
               get_the_title(),
               $post->jw_salon_booking_ydelse_pris,
               $post->jw_salon_booking_varighed );
      }
    }        
    return $items;
  }
  /**
   * load styles and scripts for admin
   *
   * @return void
   */
  
  public function load_jw_salon_booking_wp_admin_style_and_script($hook) {
    wp_enqueue_style ( 'jw_salon_booking_wp_admin_css', plugins_url('/css/admin-styles.css', __FILE__) );

    wp_register_script ( 'jw_salon_booking_admin_script', plugins_url('/js/admin-script.js', __FILE__), array( 'jquery' ) );
 		$tbc_pluginurl = plugins_url( '/jw-salonbooking-bookinger.php', dirname(__FILE__) );
		wp_localize_script ('jw_salon_booking_admin_script', 'bookingURL', array( 'pluginurl' => $tbc_pluginurl ));
		wp_enqueue_script ('jw_salon_booking_admin_script');

    wp_enqueue_style ( 'trailerbookingcalendar_styles', plugins_url( '/css/fullcalendar.min.css', __FILE__ ) );
    wp_enqueue_script ( 'trailerbookingcalendar_fullcalendar_moment', plugins_url( '/js/moment.min.js', __FILE__ ) );
    wp_enqueue_script ( 'trailerbookingcalendar_fullcalendar', plugins_url( '/js/fullcalendar.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script ( 'trailerbookingcalendar_fullcalendar_danish', plugins_url( '/js/da.js', __FILE__ ), array( 'jquery' ) );
  }

  /**
   * load styles and scripts for front end
   *
   * @return void
   */

  public function load_jw_salon_booking_style() {
    wp_enqueue_style( 'jw_salon_booking_css', plugins_url('/css/jw_salon_booking-styles.css', __FILE__) );
    wp_enqueue_script ( 'jw_salon_booking_script', plugins_url('/js/jw_salon_booking-script-frontend.js', __FILE__), array('jquery'), null, true );
  }

  /**
	 * Register custom post type and taxonomy + image for taxonomy
   * Register meta values 
	 *
	 * @return void
	 */
  
  public function jw_salon_booking_registrer_cpt() {
    require_once (dirname( __FILE__ ) .'/register-post-type.php');  
    require_once (dirname( __FILE__ ) .'/register-taxonomy.php');
    require_once (dirname( __FILE__ ) .'/register-image-for-taxonomy.php');
  }

    /**
	 * load textdomain
	 *
	 * @return void
	 */
	public function jw_salonbooking_textdomain() {
		load_plugin_textdomain( 'jw_salonbooking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
 
}


