<?php

// Shortcodes til at vise hold og tranere både alle og single
//
// [forsidehold]
// [forsidetraenere]
// [singlehold]
// [singletraener]


// http://php.net/manual/en/function.array-multisort.php
function jw_salon_booking_array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}



function jw_salon_booking_forsidehold( $attr ) { 
  $fch = shortcode_atts(array (
      'overskriften' => 'Holdoversigt',
      'sorter_efter' => 'post_title',
			'side-valg' => 'forside', // kan sættes til holdside eller forside for at bruge anden css class
			'ved-hver' => '4', 
  ), $attr);
	  
  ob_start();
    require_once ( plugin_dir_path( __FILE__ ) . 'templates/vis-holdene.php' );
  return ob_get_clean();
}

add_shortcode( 'jw_salon_booking_forsidehold', 'jw_salon_booking_forsidehold' );

// alle trænere

function jw_salon_booking_forsidetraenere( $attr ) { 
  $a = shortcode_atts(array (
      'overskriften' => 'Holdoversigt',
      'sorter_efter' => 'post_title',
  ), $attr);
  
  ob_start();
?>
    <div class="fch-forside-container">
      <div class="fch-forside-visholdbeskrivelse">
				<ul class="forside-holdoversigt">
						<?php
					$args = array(
							'post_type' => 'traener',
							'posts_per_page' => -1,
							'orderby' => 'post_title',
							'order' => 'ASC',        
						);
						$my_query = new WP_Query($args);
						if ($my_query->have_posts()) {
							$fch_counter == 0;
							while ($my_query->have_posts()) : $my_query->the_post();
								$fch_counter++;
								if ($fch_counter == 4) {
									echo '<li class="forside-hold forside-traener fjerde-traener"><a href="'.get_the_permalink().'" class="holdlink"><div class="holdbilledecontainer"><img src="'.get_the_post_thumbnail_url().'" class="holdbilledet traenerbilledet"></div><div class="holdtitel">'.esc_html( get_the_title() ).'</div></a></li>';
									$fch_counter = 0;
								} else {
									echo '<li class="forside-hold forside-traener"><a href="'.get_the_permalink().'" class="holdlink"><div class="holdbilledecontainer"><img src="'.get_the_post_thumbnail_url().'" class="holdbilledet traenerbilledet"></div><div class="holdtitel">'.esc_html( get_the_title() ).'</div></a></li>';
								}
							endwhile;
						} 
						wp_reset_query();
					?>
				</ul> 
			</div>
		</div>
<?php
  return ob_get_clean();
}

add_shortcode( 'jw_salon_booking_forsidetraenere', 'jw_salon_booking_forsidetraenere' );

function jw_salon_booking_singletraener ( $atrr ) {
	$traener_name = get_the_title();
	ob_start(); ?>
	    <div class="fch-container">
      <div class="fch-visholdbeskrivelse">
        <h1><?php the_title(); ?></h1>
        <img class="fch-postthumbnail" src="<?php the_post_thumbnail_url();?>">
        <?php the_content(); ?>
      </div>
      <div class="fch-vistraenerbeskrivelse">
          <h4>Hvilke hold træner <?php echo $traener_name;?></h4>        
          <ul class="forside-holdoversigt">
            <?php
					  $args = array(
							'post_type' => 'hold',
							'posts_per_page' => -1,
							'orderby' => 'post_title',
							'order' => 'ASC',
              'meta_key' => 'traener_name',
              'meta_value' => $traener_name,
						);
            $my_query = new WP_Query($args);
						if ($my_query->have_posts()) {
							while ($my_query->have_posts()) : $my_query->the_post();
								echo '<li class="forside-hold"><a href="'.get_the_permalink().'" class="holdlink"><div class="holdbilledecontainer"><img src="'.get_the_post_thumbnail_url().'" class="holdbilledet"></div><div class="holdtitel">'.esc_html( get_the_title() ).'</div></a></li>';
							endwhile;
						} 
						wp_reset_query();
					?>
        </ul> 
      </div>
    </div>
<?php 
	return ob_get_clean();
}

add_shortcode ('jw_salon_booking_singletraener', 'jw_salon_booking_singletraener');

function jw_salon_booing_singlehold ( $atrr ) {
	  $selectedtrainer = get_post_meta($post->ID, 'traener_name');
    $mandag = get_post_meta($post->ID, 'mandag', true);	
    $tirsdag = get_post_meta($post->ID, 'tirsdag', true);	
    $onsdag = get_post_meta($post->ID, 'onsdag', true);	
    $torsdag = get_post_meta($post->ID, 'torsdag', true);	
    $fredag = get_post_meta($post->ID, 'fredag', true);	
    $lordag = get_post_meta($post->ID, 'lordag', true);	
    $sondag = get_post_meta($post->ID, 'sondag', true);	
    $holdstartmandag = get_post_meta($post->ID, 'holdstartmandag', true);
    $holdslutmandag = get_post_meta($post->ID, 'holdslutmandag', true);
    $holdstarttirsdag = get_post_meta($post->ID, 'holdstarttirsdag', true);
    $holdsluttirsdag = get_post_meta($post->ID, 'holdsluttirsdag', true);
    $holdstartonsdag = get_post_meta($post->ID, 'holdstartonsdag', true);
    $holdslutonsdag = get_post_meta($post->ID, 'holdslutonsdag', true);
    $holdstarttorsdag = get_post_meta($post->ID, 'holdstarttorsdag', true);
    $holdsluttorsdag = get_post_meta($post->ID, 'holdsluttorsdag', true);
    $holdstartfredag = get_post_meta($post->ID, 'holdstartfredag', true);
    $holdslutfredag = get_post_meta($post->ID, 'holdslutfredag', true);
    $holdstartlordag = get_post_meta($post->ID, 'holdstartlordag', true);
    $holdslutlordag = get_post_meta($post->ID, 'holdslutlordag', true);
    $holdstartsondag = get_post_meta($post->ID, 'holdstartsondag', true);
    $holdslutsondag = get_post_meta($post->ID, 'holdslutsondag', true);
    
		ob_start();
?>
    <div class="fch-container">
      <div class="fch-visholdbeskrivelse">
        <h1><?php the_title(); ?></h1>
        <div class="tidspunkt">
          <h4>
            Træningstider
          </h4>
          <?php
          if ($mandag == 'mandag') { echo "Mandag: ".$holdstartmandag." - ".$holdslutmandag."<br/>"; }
          if ($tirsdag == 'tirsdag') { echo "Tirsdag: ".$holdstarttirsdag." - ".$holdsluttirsdag."<br/>"; }
          if ($onsdag == 'onsdag') { echo "Onsdag: ".$holdstartonsdag." - ".$holdslutonsdag."<br/>"; }
          if ($torsdag == 'torsdag') { echo "Torsdag: ".$holdstarttorsdag." - ".$holdsluttorsdag."<br/>"; }
          if ($fredag == 'fredag') { echo "Fredag: ".$holdstartfredag." - ".$holdslutfredag."<br/>"; }
          if ($lordag == 'lordag') { echo "Lørdag: ".$holdstartlordag." - ".$holdslutlordag."<br/>"; }
          if ($sondag == 'sondag') { echo "Søndag: ".$holdstartsondag." - ".$holdslutsondag."<br/>"; }
          ?>
        </div>
        <img class="fch-postthumbnail" src="<?php the_post_thumbnail_url();?>">
        <?php the_content(); ?>
      </div>
      <div class="fch-vistraenerbeskrivelse">
          <h4>Holdets træner</h4>        
          <?php
					$st = $selectedtrainer[0];
					$selectedtrainer = explode (",", $st);
					foreach ($selectedtrainer as $trainer => $trainer_name) { 
						$args = array(
							'post_type' => 'traener',
							'posts_per_page' => -1,
							'orderby' => 'post_title',
							'order' => 'ASC',
              'title' => $trainer_name,
						);
						$my_query = new WP_Query($args);
						if ($my_query->have_posts()) {
							while ($my_query->have_posts()) : $my_query->the_post();
                echo '<div class="vistraener"><div class="traenerbillede"><img class="traenerfoto" src="'.get_the_post_thumbnail_url().'"></div>';
                echo '<div class="traenertitel"><h3>';
                the_title();
                echo '</h3></div></div>';
                echo '<div class="traener_laes_mere"><a href="'.get_permalink().'">Læs mere om '.the_title().'</div>';
							endwhile;
						} 
						wp_reset_query();
					}
					?>
      </div>
    </div>

<?php
	return ob_get_clean();

}

add_shortcode ( 'jw_salon_booing_singlehold','jw_salon_booing_singlehold' );