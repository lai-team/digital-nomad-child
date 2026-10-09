<?php get_header(); 
    $geom_point = json_decode(get_post_meta( get_the_ID(),'geom_point',true ));
    $coordinates = $geom_point->geometry->coordinates;
?>

<style>
    #header{
        background-image: url("<?php echo "https://api.mapbox.com/styles/v1/" . MAPBOX_STYLE_SATELLITE  ."/static/$coordinates[0],$coordinates[1],14,0.00,0.00/1000x600@2x?access_token=". MAPBOX_TOKEN ?>");
    }
</style>

<section id="main">
	<div id="content" <?php post_class( 'digitalnomad_post_container' ); ?>>
		<?php
		do_action( 'digitalnomad_before_inner_content' );

		if ( have_posts() ) :
			while ( have_posts() ) :

				the_post();
				get_template_part( 'templates/post', 'single' );

			endwhile;
		else:
			get_template_part( 'templates/post', 'none' );
		endif;

		do_action( 'digitalnomad_after_inner_content' );
		?>
	</div>
</section><!-- #main -->
<?php
get_sidebar();
get_footer();
