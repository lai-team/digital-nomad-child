<?php
get_header();

/*
 * A post with no geom_point meta used to produce four PHP 8 warnings here --
 * two reading ->geometry->coordinates on null, two taking array offsets on
 * null -- and emitted a Mapbox URL with empty coordinates, which 4xxs and
 * leaves a blank header. Emit the style block only when there is a real
 * coordinate pair, and only when the constants exist.
 */
$bv_geom_point  = json_decode( get_post_meta( get_the_ID(), 'geom_point', true ) );
$bv_coordinates = isset( $bv_geom_point->geometry->coordinates ) && is_array( $bv_geom_point->geometry->coordinates )
	? $bv_geom_point->geometry->coordinates
	: array();

if ( count( $bv_coordinates ) >= 2 && defined( 'MAPBOX_STYLE_SATELLITE' ) && defined( 'MAPBOX_TOKEN' ) ) :
	$bv_header_image = sprintf(
		'https://api.mapbox.com/styles/v1/%s/static/%s,%s,14,0.00,0.00/1000x600@2x?access_token=%s',
		MAPBOX_STYLE_SATELLITE,
		rawurlencode( $bv_coordinates[0] ),
		rawurlencode( $bv_coordinates[1] ),
		rawurlencode( MAPBOX_TOKEN )
	);
	?>
<style>
    #header{
        background-image: url("<?php echo esc_url( $bv_header_image ); ?>");
    }
</style>
<?php endif; ?>

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
