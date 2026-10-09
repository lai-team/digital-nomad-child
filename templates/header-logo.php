<?php
$custom_logo_id	 = get_theme_mod( 'custom_logo' );
$custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'medium_large' );
$alt			 = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
//error_log(print_r('author: '. json_encode(get_queried_object()),true));
?>

<a href="<?php echo esc_url( home_url() ); ?>" rel="home" class="logo_link <?php if(is_singular()) echo 'hide'; ?>">

	<?php if ( !empty( $custom_logo_id ) ): ?>

		<img src="<?php echo esc_url( $custom_logo_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" title="<?php echo esc_attr( $alt ); ?>" class="logo">

	<?php else: ?>

		<?php echo get_avatar(  get_queried_object()->post_author, 200, 'mp', $alt, array( 'class' => 'logo', 'extra_attr' => 'title=\'' . esc_attr( $alt ) . '\'' ) ); ?>

	<?php endif; ?>

</a>
