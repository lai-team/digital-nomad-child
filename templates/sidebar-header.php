<?php
/**
 * Displays the sidebar header with search feature and menu items
 *
 */

?>

<div class="header-inner section-inner">
	<div class="header-titles-wrapper">
        <?php 
            get_template_part( 'templates/sidebar-search' ); 
            get_template_part( 'templates/sidebar-menu' ); 
            get_template_part( 'templates/sidebar-modal-search' );
            get_template_part( 'templates/sidebar-modal-menu' );
        ?>
	</div><!-- .header-titles-wrapper -->
</div><!-- .header-inner -->
