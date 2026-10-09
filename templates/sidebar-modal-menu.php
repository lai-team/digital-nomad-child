<?php
/**
 * Displays the menu modal and categories listed
 *
 */

?>

<div class="menu-modal show-modal header-footer-group">

	<div class="menu-modal-inner modal-inner">

		<div class="menu-wrapper section-inner">

			<div class="menu-top">

				<button class="close-nav-toggle fill-children-current-color" data-toggle-target=".menu-modal" data-toggle-body-class="showing-menu-modal" aria-expanded="false" data-set-focus=".menu-modal">
					<span class="toggle-text"><?php _e( 'Close Menu', 'digital-nomad' ); ?></span>
					<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <polygon fill="" fill-rule="evenodd" points="6.852 7.649 .399 1.195 1.445 .149 7.899 6.602 14.352 .149 15.399 1.195 8.945 7.649 15.399 14.102 14.352 15.149 7.899 8.695 1.445 15.149 .399 14.102"></polygon>
                    </svg>
				</button><!-- .nav-toggle -->

				<?php
					get_template_part( 'templates/category_list_widget' );
				?>

			</div><!-- .menu-top -->

		</div><!-- .menu-wrapper -->

	</div><!-- .menu-modal-inner -->

</div><!-- .menu-modal -->
