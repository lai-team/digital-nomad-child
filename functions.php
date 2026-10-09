<?php


/*
 * Enqueue styles or scripts
 */

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
function my_theme_enqueue_styles() {
	$parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array($parent_style), wp_get_theme()->get('Version'));
	wp_enqueue_script('child-script', get_stylesheet_directory_uri() . '/assets/js/main.js',array('jquery'));
	wp_enqueue_script('bv-list-stories-script', get_stylesheet_directory_uri() . '/assets/js/list-stories.js',array('jquery','bv-map-js'));
	wp_enqueue_style('sidebar-modal-menu-style', get_stylesheet_directory_uri() . '/assets/css/sidebar-modal-menu.css', array($parent_style), wp_get_theme()->get('Version'));

	$google_font_settings = json_decode(file_get_contents(get_stylesheet_directory() . "/assets/fonts/google-font-settings.json"));

	foreach($google_font_settings->google_font_types_to_include as $key => $type) {
		wp_enqueue_style( 'google-fonts-' . $key, $google_font_settings->google_font_url . $type, false );
	}

	$dtp = array(
		'template_path'  => get_stylesheet_directory_uri(),
		'test'	=>'test',
	);
	wp_localize_script( 'child-script', 'pv', $dtp );
}

//add_action( 'digitalnomad_before_inner_content', 'dn_theme_top' );
function dn_theme_top(){


}

//add_action('digitalnomad_after_inner_content','dn_theme_bottom' ); 
function dn_theme_bottom(){


}


/*
 * Add MAP tag in header
 */

add_action('add_map_for_post', 'addMapForPost');

function addMapForPost() {
	if(!is_home(  ))
	if (!is_archive(  )) {
		return;
	}
	echo "<div class='post_map_image_container'>";
	echo do_shortcode("[bv_map_shortcode post_id='" . get_the_ID() . "' height='40vh']");
}

function digitalnomad_basic_footer_text() {

?>
      <p>
	  <?php echo esc_html( date( 'Y', time() ) ); ?> &copy; <?php bloginfo( 'name' ); ?>.
<?php
	/* translators: %s: WordPress homepage link */
	printf( esc_html__( 'Powered by %s.', 'digital-nomad' ), '<a href="https://beau.voyage/" target="_blank">beauVoyage</a>' );
?>
      </p>
<?php

}

/**
 * Custom get_the_date function
 */
function digitalnomad_get_the_date() {
	$code = '';
	$updated = esc_attr( get_the_modified_time( 'U' ) );
	$date_format = esc_attr( get_option( 'date_format' ) );
	if( get_post_type() == EVENTS_POSTTYPE){
		$published = strtotime(get_post_meta(get_the_ID() , '_EventStartDate',true));
		$enddate = strtotime(get_post_meta(get_the_ID() , '_EventEndDate',true));
		$duration = sprintf( esc_html__( ' %s', 'digital-nomad' ), human_time_diff( $published, $enddate ) );    
		$code .= '<time class="published" datetime="' . date( 'c', $published ) . '">' . date( $date_format, $published ) . " &mdash " . date($date_format,$enddate  ) . '</time>';
		$code .= ', <span class="last_update_text">' . __( 'for', 'digital-nomad' ) . '</span> <time class="updated" datetime="' . date( 'c', $published ) . '">' . $duration . '</time>';
	}else{
		$published = esc_attr( get_the_date( 'U' ) );
		$code .= '<time class="published" datetime="' . date( 'c', $published ) . '">' . date( $date_format, $published ) . '</time>';
		if ($published != $updated) {
			$code .= ', <span class="last_update_text">' . __( 'written:', 'digital-nomad' ) . '</span> <time class="updated" datetime="' . date( 'c', $updated ) . '">' . date_i18n( $date_format, $updated ) . '</time>';
		}
	}
	return $code;
}

//add_action( 'digitalnomad_for_footer', 'digitalnomad_basic_footer_text' );
function digitalnomad_get_the_date_ago() {
	$date_format = get_option( 'date_format' );
	$code = '';
	$now = current_time( 'timestamp' );
	if( get_post_type() == EVENTS_POSTTYPE ){
		$published = strtotime(get_post_meta(get_the_ID() , '_EventStartDate',true));
		$enddate = strtotime(get_post_meta(get_the_ID() , '_EventEndDate',true));
		if ($now < $published ){
			//togo
			$ago = sprintf( esc_html__( '%s to go', 'digital-nomad' ), human_time_diff( current_time( 'timestamp' ),$published ) );
		}elseif($now > $enddate){
			//ago 
			$ago = sprintf( esc_html__( '%s ago', 'digital-nomad' ), human_time_diff( $enddate, current_time( 'timestamp' ) ) );
		}else{//now
			$ago = sprintf( esc_html__( 'started %s ago', 'digital-nomad' ), human_time_diff( $published, current_time( 'timestamp' ) ) );
		}
	}else{
		$published = get_the_date( 'U' );
		/* translators: %s: time difference in human-readable way */
		$ago = sprintf( esc_html__( '%s ago', 'digital-nomad' ), human_time_diff( $published, current_time( 'timestamp' ) ) );
	}
	$code .= '<time class="published" datetime="' . date( 'c', $published ) . '" title="' . date( $date_format, $published ) . '">' . date( $date_format, $published ) . '<br>'. $ago. '</time>';

	return $code;
}

function digitalnomad_get_forecast_days(){
		$dt_start = strtotime(get_post_meta(get_the_ID() , '_EventStartDate',true));
		$dt_end = strtotime(get_post_meta(get_the_ID() , '_EventEndDate',true));
		$dt_current = current_time( 'timestamp' );
		$dt_forecast_start = max($dt_start , $dt_current);
		$dt_forecast_end = min($dt_forecast_start + (86400 * 6) , $dt_end, FORECAST_DAYS_AHEAD_BASIC + $dt_current);
		$dt_duration = $dt_forecast_end - $dt_forecast_start;
		if($dt_duration > 0){
			$days = ceil( $dt_duration / 86400 );
			if( ($days < 7) && ($dt_start > $dt_current )) $dt_forecast_start-=86400;
			if( ($days < 6) && ($dt_forecast_end < FORECAST_DAYS_AHEAD_BASIC + $dt_current - 86400) ) $dt_forecast_end+=86400; 
			return array($dt_forecast_start, $dt_forecast_end);
		}else return;
		//define('FORECAST_DAYS_AHEAD_PREMIUM', 86400 * 15);
		//define('FORECAST_DAYS_AHEAD_BASIC', 86400 * 15);
}

/*
function digitalnomad_get_the_date_ago() {
	$code = '';
	$published = get_the_date( 'U' );
	$date_format = get_option( 'date_format' );
	// translators: %s: time difference in human-readable way 
	$ago = sprintf( esc_html__( '%s ago', 'digital-nomad' ), human_time_diff( $published, current_time( 'timestamp' ) ) );
	$code .= '<time class="published" datetime="' . date( 'c', $published ) . '" title="' . date( $date_format, $published ) . '">' . date( $date_format, $published ) . '<br>'. $ago. '</time>';

	return $code;
}
 */
function dn_loadn_preview($post_id,$previous=true,$load_N=5,$journey_id=null){
	global $post; 
        $post = get_post($post_id);
        $journey_ids = $journey_id?array($journey_id):array();
        $htmlArray = array();
        $coordArray = array();
        $returnArray = array();
	#if($journey_id) $previous=false;
	$query = bv_get_adjac_post($post,$previous,$journey_ids,'',$load_N);
	if( $query->have_posts() ) {
		    while ( $query->have_posts() ) { 
			        $query->the_post();
				if($geom_point = bv_get_geompoint($query->post)){
                                	$coordArray[] = $geom_point;
				}
                        $htmlArray[] = digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		        wp_reset_postdata();
	}

        if(!$previous){
                $htmlArray = array_reverse($htmlArray);
                $coordArray = array_reverse($coordArray);
        }
        $returnArray['html'] = implode(PHP_EOL,$htmlArray);
        $returnArray['coords'] = $coordArray;

        //echo json_encode($returnArray);
	return $returnArray;
}

function dn_loadn_preview_for_ajax($post_id,$previous=true,$load_N=5,$journey_id=null){
	global $post; 
        $post = get_post($post_id);
        $journey_ids = $journey_id?array($journey_id):array();
        $htmlArray = array();
        $coordArray = array();
        $returnArray = array();
	#if($journey_id) $previous=false;
        do{
                $new_post = bv_get_adjacent_post( $post, $previous,  $journey_ids);
                   //logErrors($load_N . ' '.$new_post->post_title . ' tiTle '.$post->post_title);
                if( $new_post && ($new_post->ID != $post->ID )){
                        $post=$new_post;
                        if($geom_point = bv_get_geompoint($post) ){
                                $coordArray[] = $geom_point;
                        }
                        $htmlArray[] = digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        $load_N -= 1;
                }else{
                        $post= $new_post;
                }
        }while( $new_post && $load_N > 0);
        if(!$previous){
                $htmlArray = array_reverse($htmlArray);
                $coordArray = array_reverse($coordArray);
        }
        $returnArray['html'] = implode(PHP_EOL,$htmlArray);
        $returnArray['coords'] = $coordArray;

        //echo json_encode($returnArray);
	return $returnArray;
}

function digitalnomad_post_preview_layout_for_ajax() {
	if (!has_excerpt()) {
		$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( '' ), true ), 22 );
	} else {
		$excerpt = trim( wp_strip_all_tags( get_the_excerpt(), true ) );
	}

	if (has_post_thumbnail()) {
		$feature_image_arr = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		$feature_image_src = $feature_image_arr[ 0 ];
	} elseif (get_theme_mod( 'digitalnomad_default_feature_image' )) {

		$feature_image_arr = wp_get_attachment_image_src( get_theme_mod( 'digitalnomad_default_feature_image' ), 'large' );
		$feature_image_src = $feature_image_arr[ 0 ];
	} else {
		//$feature_image_src = null; // because digitalnomad_default_feature_image may not be set
		if ($geom_point = json_decode(get_post_meta( get_the_ID(),'geom_point',true )) ){
			$coordinates = $geom_point->geometry->coordinates;
			$feature_image_src = "https://api.mapbox.com/styles/v1/" . MAPBOX_STYLE_SATELLITE  ."/static/$coordinates[0],$coordinates[1],14,0.00,0.00/1000x600@2x?access_token=". MAPBOX_TOKEN;
		}
	}

	$style = !empty( $feature_image_src ) ? 'background-image: url(\'' . esc_url( $feature_image_src ) . '\');' : '';

	$layout = '';

	if (get_theme_mod( 'digitalnomad_blazy_lazyload', true )) {
		$layout .= '<article id="post_' . get_the_ID() . '" class="' . implode( ' ', get_post_class( 'preview_content basic_bg b-lazy' ) ) . '" style="background-image: url(\'' . $feature_image_src . '\')">' . PHP_EOL;
		$layout .= '<noscript><style>#post_' . get_the_ID() . ' {' . $style . '}</style></noscript>' . PHP_EOL;
	} else {
		$layout .= '<article class="' . implode( ' ', get_post_class( 'preview_content basic_bg' ) ) . '" style="' . $style . '">' . PHP_EOL;
	}
	$layout .= ' <a href="' . esc_url( get_the_permalink() ) . '" title="' . esc_attr( $excerpt ) . '">' . PHP_EOL;
	$layout .= '  <div class="preview_inside">' . PHP_EOL;
	$layout .= '   <div class="preview_time main_time text_shadow">' . digitalnomad_get_the_date_ago() . '</div>' . PHP_EOL;
	$layout .= '   <h2 class="preview_title main_title text_shadow">' . esc_html( get_the_title() ) . '</h2>' . PHP_EOL;
	$layout .= '  </div>' . PHP_EOL;
	$layout .= ' </a>' . PHP_EOL;
	$layout .= '</article>' . PHP_EOL;

	return $layout;

}

add_shortcode( 'bv_search_widget', 'bv_search_widget' );
function bv_search_widget(){
	get_template_part( 'templates/sidebar-header' );
	//$content = "";
	//$content .= "<div class='bv-search-container'>";
	//$content .= "<input type='text' id='bs-search-input' placeholder='Search'>";
	//$content .= "</div>";
	// $content .=
	//return $content;
}

add_action('digitalnomad_preview_layout', 'digitalnomad_preview_layout');
function digitalnomad_preview_layout($top=true){
	$layout = '';
	if($top){
		get_header();
		echo '<section id="main"> ';
		echo do_shortcode( '[bv_search_widget]' );
		$layout .= ' <div id="content" class="digitalnomad_post_container"> ';
		$layout .= '<span id="load-more-top" style="width: 100%;height: 1px;margin-bottom: -100vh;display: block;"></span>';
		echo $layout;
		do_action( 'digitalnomad_before_inner_content' );  
		echo '<div id="journey-story-wrapper">';
	}else{
		$layout .= '</div>';
		$layout .= '<div id="search-story-wrapper">';
		$layout .= '</div>';
		echo $layout;
		do_action('digitalnomad_after_inner_content');
		if(!is_home(  ))echo "<input type='hidden' id='journey_id' value=".get_queried_object(  )->term_id.">";
		echo '<span id="load-more-bottom" style="width: 100%;height: 1px;margin-top: -100vh;display: block;"></span> </div> ';
		echo digitalnomad_posts_nav_link(); 
		echo '</section>';
		get_sidebar();
		get_footer();
	}
}

function digitalnomad_preview_layout_for_post($post_obj){
	global $post;
	$post = $post_obj;
	return digitalnomad_post_preview_layout_for_ajax(); 
}
