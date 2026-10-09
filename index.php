<?php get_header(); ?>
<section id="main">
    <?php echo do_shortcode( '[bv_search_widget]' ); ?>
    <div id="content" class="digitalnomad_post_container">
	<span id="load-more-top" style="width: 100%;height: 1px;margin-bottom: -100vh;display: block;"></span>
        <?php
	$initCoords = array();
        do_action( 'digitalnomad_before_inner_content' );
	?>
	<?php
        echo "<div id='journey-story-wrapper'>";
        if(is_home(  )){
		/*
            $terms = get_terms( 'category', array('hide_empty' => false) );
            foreach($terms as $term){
                // var_dump($term);
                $args = array(
                    'cat' =>$term->term_id,
                    'limit' => 1
                );
                $journey = new WP_Query($args);
                if($journey->have_posts()){
                    $qo_url = get_category_link($term->term_id);
                    echo "<div class='journey-name' data-id='$term->term_id'>
                    <a href='$qo_url'><span>$term->name</span></a>
                    <p class='journet-description'>$term->description</p>
                    <!-- <button class='expand-journey' onclick='expandJourney(this)'>Expand</button> -->
                    <div class='stories-wrapper'>";
                    // while($journey->have_posts()){
                    //     $journey->the_post();
                    //     echo digitalnomad_post_preview_layout(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    //     // the_title(  );
                    // };
                    echo "</div>
                    </div><hr style='margin:0'>";
                   
                }
	    }
		 */
            $post_list = new WP_Query(
                array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 5,
                )
            );
            if($post_list->have_posts()){
                echo "<div class='story-list'>";
                while($post_list->have_posts()){
                    $post_list->the_post(  );
		    $initCoords[]=bv_get_geompoint($post);
                    echo digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                echo "</div>";
            }
        }
        else
        if ( have_posts() ) :
            if($qo = get_queried_object(  )){
                $qo_url = get_category_link($qo->term_id);
                echo "<div class='journey-name'><a href='$qo_url'><span>$qo->name</span></a><p class='journet-description'>$qo->description</p></div>";
                // var_dump(get_queried_object(  ));
            }
            echo "<div class='story-list'>";

            while ( have_posts() ) :
                the_post();
		    $initCoords[]=bv_get_geompoint($post);
                // digitalnomad_post_preview_layout function is delivered already escaped
                echo digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            endwhile;
            echo "</div>";

        else:
            get_template_part( 'templates/post', 'none' );
        endif;
        echo "</div>";
        echo "<div id='search-story-wrapper'>";
        echo "</div>";
        do_action( 'digitalnomad_after_inner_content' );
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
	wp_localize_script('bv-list-stories-script','initCoords',$initCoords);
	//wp_localize_script('bv-list-stories-script','initQuery', get_queried_object);
	//logErrors('init c00rds '.json_encode($initCoords));
        ?>
        <span id="load-more-bottom" style="width: 100%;height: 1px;margin-top: -100vh;display: block;"></span>
    </div>
    <!-- <span id="sentinel"></span> -->
<?php echo digitalnomad_posts_nav_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</section><!-- #main -->
<?php
get_sidebar();
get_footer();
