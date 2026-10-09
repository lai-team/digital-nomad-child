<?php
/**
 * Get categories and list the categories inside of the menu feature
 */
?>

<?php
    /**
     * Loading settings file with the list of categories that needs to be excluded from the category list widget
     */
    $category_exclude_list = json_decode(file_get_contents(dirname(__FILE__) . "/category_exclude_settings.json"));
    
    /**
     * Function used for excluding categories from the category widget
     * 
     * @param - slug of the category to be excluded from the category widget
     * @param - category_exclude_settings json containing list of categories to be excluded
     * 
     * @return - true/false if the category with slug needs to be excluded or not
     * 
     */
    function exclude($slug, $category_exclude_list) {
        foreach($category_exclude_list->category_exclude_list as $item) {
            if($slug === $item->slug) {
                return true;
            }
        }
        return false;
    }

    /*
    if(isset($_get['order']) == 'asc'){
	        query_posts('order=asc');
		  }else if(isset($_get['order']) == 'desc'){
			      query_posts('order=desc');
		  }
     */
    function bv_change_posts_order( $query ){
	        if ( ! is_admin() && ( is_category() || is_tag() ) && $query->is_main_query() ) {
			        $query->set( 'order', 'ASC' );
				    }
    };
    add_action( 'pre_get_posts', 'bv_change_posts_order'); 

    /**
     * Function for creating cagetegory list in hierarchical order
     * 
     * @param - $parent_id - category id to start from
     * @param - $category_list - array passed by reference to store the list of all categories ordered in hierarchical
     * @param - $hierarchy_level - hierachy level used for defining the level of categories in hierarchy
     * 
     * @return - $category_list array passed to the function will contain all of the categories listed in hierachical order
     * 
     */
    function get_categories_hierarchy ($parent_id, &$category_list, $hierarchy_level) {

        $categories = get_categories(
            array(
                'taxonomy' => 'category', // Taxonomy to retrieve terms for. We want 'category'. Note that this parameter is default to 'category', so you can omit it
                'orderby' => 'name',
                'parent' => $parent_id,
                'hide_empty' => 0, // change to 1 to hide categores not having a single post
            )
        );

        $hierarchy_level = $hierarchy_level + 1;

        if($categories) {
            if($parent_id !== 0) {
                //log if category has child categories, settings to be used for styling of the sub-category menu
                $category_list["category_id_" . $parent_id]['has_child_categories'] = 1;
            } 
            //Listing out categories
            foreach ( $categories as $category ) {
                
                $category_list["category_id_" . $category->cat_ID] = array (
                    "term_id" => $category->term_id,
                    "name" => $category->name,
                    "slug" => $category->slug,
                    "hierarchy_level" => $hierarchy_level,
                    "parent" => $category->parent,
                    "has_child_categories" => 0
                );
                
                //Get child categories of the current category
                get_categories_hierarchy($category->cat_ID, $category_list, $hierarchy_level);
            }
        }
    }

    /**
     * Function for defining and returning the closure HTML for sub-category menus
     * 
     * @param - current category parent id - parent id of the current category in the loop
     * @param - parent category id - parent category id defined when category has child categories 
     *          and is being used to define which sub-category menu to close
     * @param - array of parent sub-category menus to be closed if next item in menu is hierarchy level 1 item
     * 
     * @return - HTML for closing ul and li elemets
     * 
     */

    function sub_category_list_closure ($current_category_parent_id, $parent_category_id, &$submenu_closure) {

        $closure_html = "";

        //Logic for closing sub-category menus
        if($parent_category_id !== $current_category_parent_id) {
            if($current_category_parent_id === 0){
                foreach($submenu_closure as $closure) {
                    $closure_html .= "</ul></li>";
                    array_pop($submenu_closure);
                }
            } else {
                array_pop($submenu_closure);
                $closure_html .= "</ul></li>";
            }
        }

        return $closure_html;
    }
?>

<nav class="expanded-menu mobile-menu" role="navigation">
    <ul class="modal-menu reset-list-style">
        <li class="page_item">
            <a href="<?php echo get_home_url(); ?>" class="<?php echo ( is_home() ) ? 'active' : 'not-active' ?>">Main Page</a>
        </li>
        <?php
            // Get the current queried object
            $term    = get_queried_object();
            $term_id = ( isset( $term->term_id ) ) ? (int) $term->term_id : 0;

            //Array containing list of categories in hierarchical order
            $categories = array();
            //Variable for storing the parent category id used to close the submenu if parent category has child categories
            $parent_category_id = 0;
            //Array containing list of parent category ids used to close all of the remaining submenu lists
            $submenu_closure = array();

            //Function populating $categories array with list of categories in hierarchical order
            get_categories_hierarchy(0, $categories, 0);

            //Listing out categories
            foreach ( $categories as $category ) {
                $cat_ID = (int) $category['term_id'];
                $category_name = $category['name'];

                // When viewing a particular category, give it an [active] class
                $cat_class = ( $cat_ID == $term_id ) ? 'active' : 'not-active';
                //Define hierarchy level class
                $hierarchy_class = "hierarchy_level_" . $category['hierarchy_level'];

                //Do not show the [uncategoirzed] category and category with slug = private (which should be private content)
                if(!exclude($category['slug'], $category_exclude_list)) {
                    
                    $closure_html = sub_category_list_closure($category['parent'], $parent_category_id, $submenu_closure);
                    echo $closure_html;

                    //Separate categories that needs to go in submenu for category widget
                    if($category['has_child_categories'] === 1 || $category['hierarchy_level'] !== 1) {

                        //Check if category has child categories and create submenu wrapper
                        if($category['has_child_categories'] === 1) {

                            //Define parent category
                            $parent_category_id = $cat_ID;
                            //Set array used for closing out all sub-category menus
                            array_push($submenu_closure, $cat_ID);
        ?>
                            <li class="page_item page_item_has_children <?php echo $hierarchy_class ?>">
                                <div class="ancestor-wrapper">
                                    <a 
                                        class="<?php echo esc_attr( $cat_class ); ?>"
                                        <?php
                                            //Remove link from the active category
                                            if ( $cat_class !== 'active' ) {
                                        ?>
                                                href="<?php echo esc_url( get_category_link( $category['term_id'] ) ); ?>"
                                        <?php
                                            }
                                        ?>
                                    >
                                    <?php echo esc_html( $category['name'] ); ?>
                                    </a>

                                    <button class="toggle sub-menu-toggle fill-children-current-color">
                                        <span class="screen-reader-text">Show sub menu</span>
                                        <svg class="svg-icon" aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 12">
                                            <polygon fill="" fill-rule="evenodd" points="1319.899 365.778 1327.678 358 1329.799 360.121 1319.899 370.021 1310 360.121 1312.121 358" transform="translate(-1310 -358)"></polygon>
                                        </svg>
                                    </button>
                                </div><!-- .ancestor-wrapper -->
                                <ul class="children">
                                
        <?php

                        } else {
                        //list all child categories
        ?>
                            <li class="page_item <?php echo $hierarchy_class ?>">
                                <div class="ancestor-wrapper">
                                    <a 
                                        class="<?php echo esc_attr( $cat_class ); ?>"
                                        <?php
                                            //Remove link from the active category
                                            if ( $cat_class !== 'active' ) {
                                        ?>
                                                href="<?php echo esc_url( get_category_link( $category['term_id'] ) ); ?>"
                                        <?php
                                            }
                                        ?>
                                    >
                                    <?php echo esc_html( $category['name'] ); ?>
                                    </a>
                                </div><!-- .ancestor-wrapper -->
                            </li>
        <?php           
                        }
                    } else {
        ?>
                        <li class="page_item <?php echo $hierarchy_class ?>">
                            <a
                                class="<?php echo esc_attr( $cat_class ); ?>"
                                <?php
                                    //Remove link from the active category
                                    if ( $cat_class !== 'active' ) {
                                ?>
                                        href="<?php echo esc_url( get_category_link( $category['term_id'] ) ); ?>"
                                <?php
                                    }
                                ?>
                            >
                                <?php echo esc_html( $category['name'] ); ?>
                            </a>
                        </li>
        <?php
                    }
                }
            }
        ?>
    </ul>
</nav>
