<?php
header('Content-Type:application/json');
/**
 * Makes json feed for events call from fullcalendar
 *
 */
$start = $_GET['start'];
$end = $_GET['end'];
/**
 * Call DB to see if theres registered any events between
 *  - @start (Start date)
 *  - @end (End date)
 *
 * Returning a JSON object containing all events within the requested timeperiod
 */
// - grab wp load, wherever it's hiding -
if(file_exists('../../../../wp-load.php')) :
	include '../../../../wp-load.php';
else:
	include '../../../../../wp-load.php';
endif;

global $wpdb;
$table_name = $wpdb->prefix . "jw_salon_booking_bookinger";

$start .= ' 00:00:00';
$end .= ' 23:45:00';

$sql = "SELECT * FROM ".$table_name." WHERE fra >= '".$start."' AND til <= '".$end."'";
$results = $wpdb->get_results( $sql );

// Hent info om alle ydelser
$ydelse_args = array (
    'post_type' => 'ydelse',
);

$ydelse_query = new WP_Query($ydelse_args);
$ydelse_items = array();
while ($ydelse_query->have_posts()) {
  global $post;
  $ydelse_query->the_post();
  $ydelse_items[$post->ID] => array (
    'ydelse-titel' => get_the_title(),
    'ydelse-pris' => $post->jw_salon_booking_ydelse_pris,
    'ydelse-varighed' => $post->jw_salon_booking_varighed,
  )
}

// Hent info om alle kunder??
$kunde_query = new WP_User_Query( array( 'role_in' => 'customer' ) );
while ($kunde_query->have_posts()) {
  global $post;
  $kunde_query->the_post();
  $kunde_items[$post->ID] => array (
    'kunde-navn' => $post->first_name . " " . $post->last_name,
  )
}

$tbc_events = array();
if ($results) {
  foreach($results as $result) {
     $infotext =  $kunde_items[$result->kundeid]['kunde-navn'] . '<br/>' .
                  $ydelse_items[$result->serviceid]['ydelse-titel'] . '<br/>' .
                  'Pris: '$ydelse_items[$result->serviceid]['ydelse-pris'] . 'Varighed: ' . $ydelse_items[$result->serviceid]['ydelse-varighed']
       
     $e = array();
     $e['id'] = $result->id;
     $e['title'] = $infotext;
     $e['start'] = $result->bookingstart;
     $e['end'] = $result->bookingend;
     $e['allDay'] = false;
     array_push($tbc_events, $e);
  }
  echo json_encode($tbc_events);
}
 