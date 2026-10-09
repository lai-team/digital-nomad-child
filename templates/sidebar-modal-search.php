<?php
/**
 * Displays the search icon and modal
 *
 */

?>

<div class="search-modal cover-modal header-footer-group show-modal">
	<div class="search-modal-inner modal-inner">
		<div class="section-inner">
			<div class='bv-search-container'>
				<input type='text' id='bs-search-input' placeholder='<?php _e("Search", 'digital-nomad'); ?>'>
			</div>
			<button class="toggle search-untoggle close-search-toggle fill-children-current-color">
				<span class="screen-reader-text"><?php __("Close search", 'digital-nomad'); ?></span>
				<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
					<polygon fill="" fill-rule="evenodd" points="6.852 7.649 .399 1.195 1.445 .149 7.899 6.602 14.352 .149 15.399 1.195 8.945 7.649 15.399 14.102 14.352 15.149 7.899 8.695 1.445 15.149 .399 14.102"></polygon>
				</svg>
			</button>
		</div><!-- .section-inner -->
	</div><!-- .search-modal-inner -->
</div><!-- .search-modal -->