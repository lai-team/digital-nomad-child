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
		// admin-ajax endpoint and nonce for the three handlers in inc/ajax.php.
		// These ride on the existing `pv` object because bv_map already reads
		// pv.template_path, so it can pick them up without this theme having
		// to localise anything onto a plugin's script handle.
		'ajax_url'    => admin_url( 'admin-ajax.php' ),
		'ajax_nonce'  => wp_create_nonce( 'dn_ajax' ),
	);
	wp_localize_script( 'child-script', 'pv', $dtp );
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
	// The closing tag was missing, so every element after this -- #cell, the
	// inner content, the archive title, the rest of the document -- was nested
	// inside .post_map_image_container.
	echo "</div>";
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
		$code .= '<time class="published" datetime="' . date( 'c', $published ) . '">' . date( $date_format, $published ) . " &mdash; " . date($date_format,$enddate  ) . '</time>';
		$code .= ', <span class="last_update_text">' . __( 'for', 'digital-nomad' ) . '</span> <time class="updated" datetime="' . date( 'c', $published ) . '">' . $duration . '</time>';
	}else{
		$published = esc_attr( get_the_date( 'U' ) );
		$code .= '<time class="published" datetime="' . date( 'c', $published ) . '">' . date( $date_format, $published ) . '</time>';
		if ($published != $updated) {
			// Was 'written:' -- this is the *modified* timestamp, so the label
		// asserted the opposite of the truth. The parent uses 'updated:'.
		$code .= ', <span class="last_update_text">' . __( 'updated:', 'digital-nomad' ) . '</span> <time class="updated" datetime="' . date( 'c', $updated ) . '">' . date_i18n( $date_format, $updated ) . '</time>';
		}
	}
	return $code;
}

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

function digitalnomad_post_preview_layout_for_ajax() {
	if (!has_excerpt()) {
		$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( '' ), true ), 22 );
	} else {
		$excerpt = trim( wp_strip_all_tags( get_the_excerpt(), true ) );
	}

	// Only assigned in three of the branches below; a post with no thumbnail,
	// no digitalnomad_default_feature_image theme mod and no decodable
	// geom_point left it undefined, warning and emitting url('').
	$feature_image_src = '';

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
		// esc_url() here too: the very next line already escapes the same value
		// for the <noscript> block, so this one was simply missed.
		$layout .= '<article id="post_' . get_the_ID() . '" class="' . implode( ' ', get_post_class( 'preview_content basic_bg b-lazy' ) ) . '" style="background-image: url(\'' . esc_url( $feature_image_src ) . '\')">' . PHP_EOL;
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
		/*
 * get_queried_object() is only a WP_Term on a term archive. index.php is also
 * the fallback template for date archives (null), author archives (WP_User)
 * and post-type archives (WP_Post_Type), so this warned on PHP 8 and emitted
 * `value=>`. list-stories.js then read an empty journey_id and silently
 * dropped the category restriction from every subsequent AJAX load.
 * The attribute was also unquoted.
 */
$bv_queried = get_queried_object();
if ( $bv_queried instanceof WP_Term ) {
	echo '<input type="hidden" id="journey_id" value="' . esc_attr( $bv_queried->term_id ) . '">';
}
		echo '<span id="load-more-bottom" style="width: 100%;height: 1px;margin-top: -100vh;display: block;"></span> </div> ';
		echo digitalnomad_posts_nav_link(); 
		echo '</section>';
		get_sidebar();
		get_footer();
	}
}

/* -------------------------------------------------------------------------
 * AJAX handlers
 *
 * These three actions used to live in inc/ajax.php, a file that bootstrapped
 * WordPress by path-munging __DIR__ to find wp-load.php and then read $_POST at
 * file scope. It was directly addressable at <theme-uri>/inc/ajax.php and had
 * no nonce and no capability check anywhere in the path. Confirmed against the
 * live site: an unauthenticated fetch with credentials omitted returned HTTP
 * 200 and a full payload.
 *
 * The disclosure was that get_post() does not check read permission, while
 * digitalnomad_post_preview_layout_for_ajax() emits the post title and the
 * first 22 words of its body. Any post id therefore returned that much of any
 * post, including drafts, pending and member-restricted ones.
 *
 * They now run through admin-ajax.php, so WordPress is already loaded and the
 * endpoint is not separately addressable. The action names are unchanged, so
 * they map straight onto wp_ajax_/wp_ajax_nopriv_ hooks.
 * ---------------------------------------------------------------------- */

/**
 * Whether the current request may read a given post.
 *
 * Published posts are public. Anything else requires the capability, which is
 * what closes the disclosure; the nonce is defence in depth, not authorisation.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function dn_ajax_can_read_post( $post_id ) {
	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return false;
	}

	$post_obj = get_post( $post_id );

	if ( ! $post_obj ) {
		return false;
	}

	if ( 'publish' === $post_obj->post_status ) {
		return true;
	}

	return current_user_can( 'read_post', $post_id );
}

/**
 * Shared entry checks for every handler below.
 *
 * @return array{post_id:int,journey_id:int} Sanitised request values.
 */
function dn_ajax_verify() {
	check_ajax_referer( 'dn_ajax' );

	return array(
		'post_id'    => isset( $_POST['data'] ) ? absint( $_POST['data'] ) : 0,
		'journey_id' => isset( $_POST['journey_id'] ) ? absint( $_POST['journey_id'] ) : 0,
	);
}

/**
 * Render a post and its neighbours, for jumping to a story from the map.
 */
function dn_ajax_jumpto_post() {
	$req = dn_ajax_verify();

	if ( ! dn_ajax_can_read_post( $req['post_id'] ) ) {
		wp_send_json_error( array( 'message' => 'Not found' ), 404 );
	}

	global $post;
	$post = get_post( $req['post_id'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );

	$return = array(
		'html'   => digitalnomad_post_preview_layout_for_ajax(),
		'coords' => array( bv_get_geompoint( $post ) ),
	);

	$top = dn_loadn_preview( $req['post_id'], false, 3, $req['journey_id'] );
	if ( ! empty( $top['html'] ) ) {
		$return['html'] = $top['html'] . $return['html'];
	}
	if ( ! empty( $top['coords'] ) ) {
		$return['coords'] = array_merge( $top['coords'], $return['coords'] );
	}

	$bottom = dn_loadn_preview( $req['post_id'], true, 3, $req['journey_id'] );
	if ( ! empty( $bottom['html'] ) ) {
		$return['html'] = $return['html'] . $bottom['html'];
	}
	if ( ! empty( $bottom['coords'] ) ) {
		$return['coords'] = array_merge( $return['coords'], $bottom['coords'] );
	}

	wp_reset_postdata();

	wp_send_json( $return );
}
add_action( 'wp_ajax_jumpto_post', 'dn_ajax_jumpto_post' );
add_action( 'wp_ajax_nopriv_jumpto_post', 'dn_ajax_jumpto_post' );

/**
 * Load the next or previous page of story previews.
 */
function dn_ajax_load_posts() {
	$req = dn_ajax_verify();

	if ( ! dn_ajax_can_read_post( $req['post_id'] ) ) {
		wp_send_json_error( array( 'message' => 'Not found' ), 404 );
	}

	$previous = ! empty( $_POST['previous'] ) && 'false' !== $_POST['previous'];

	wp_send_json( dn_loadn_preview( $req['post_id'], $previous, 7, $req['journey_id'] ) );
}
add_action( 'wp_ajax_load_posts', 'dn_ajax_load_posts' );
add_action( 'wp_ajax_nopriv_load_posts', 'dn_ajax_load_posts' );

/**
 * Search within a journey.
 */
function dn_ajax_load_posts_from_search() {
	dn_ajax_verify();

	$search     = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
	$journey_id = isset( $_POST['journey_id'] ) ? absint( $_POST['journey_id'] ) : 0;

	if ( '' === $search ) {
		wp_send_json( array( 'html' => '', 'coords' => array(), 'empty' => '' ) );
	}

	$args = array(
		's'           => $search,
		'post_type'   => 'post',
		'post_status' => 'publish',
	);

	if ( $journey_id ) {
		$args['cat']   = $journey_id;
		$args['order'] = 'ASC';
	}

	$journey = new WP_Query( $args );

	$return = array(
		'html'   => '',
		'coords' => array(),
	);

	while ( $journey->have_posts() ) {
		$journey->the_post();
		$return['html'] .= digitalnomad_post_preview_layout_for_ajax();
		// Was bv_get_geompoint($post) with $post never assigned in this scope.
		$return['coords'][] = bv_get_geompoint( get_post() );
	}

	wp_reset_postdata();

	if ( '' === $return['html'] ) {
		$return['empty'] = "<h3 class='bs_search_emply'>" . esc_html__( 'No posts found', 'digital-nomad-child' ) . '</h3>';
	}

	wp_send_json( $return );
}
add_action( 'wp_ajax_load_posts_from_search', 'dn_ajax_load_posts_from_search' );
add_action( 'wp_ajax_nopriv_load_posts_from_search', 'dn_ajax_load_posts_from_search' );
