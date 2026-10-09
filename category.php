<?php
do_action('digitalnomad_preview_layout', true);
	if ( true ) :
		$initCoords=array();
		$htmlArray = array();
		$post_arg = array(
			    'post_type' => 'post',
			    'post_status' => 'publish',
			    'posts_per_page' => -1,
			    'order' => 'ASC',
		    );                             
		$event_arg = array( 'posts_per_page'=>-1 );
            if($qo = get_queried_object(  )){
                $qo_url = get_category_link($qo->term_id);
                echo "<div class='journey-name'><a href='$qo_url'><span>$qo->name</span></a><p class='journet-description'>$qo->description</p></div>";
		echo "<input type='hidden' id='journey_id' value=".get_queried_object(  )->term_id.">";
                // var_dump(get_queried_object(  ));
		$event_arg += array( 'cat' => $qo->term_id);
		$post_arg += array( 'cat' => $qo->term_id);
            }
            echo "<div class='story-list'>";
	    $query = new WP_Query( $post_arg );
		$post_list = $query->posts;
	    if(isset($post_list[0])){
		    $event_arg+=array( 'start_date' => $post_list[0]->post_date );
			echo "<input type='hidden' id='last_post' value=". $post_list[0]->ID . ">";
	    }
		#$events= tribe_get_events($event_arg,false) ;
	    #if($events){
	#		 echo "<input type='hidden' id='first_event' value=". $events[0]->ID . ">";
	#		 $events = array_reverse($events);
	   
		//foreach( array_merge($events,$post_list) as $post){
		foreach( $post_list as $post){
		    	$initCoords[]=bv_get_geompoint($post);
                	echo digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}	
	   # }

		/*
            while ( have_posts() ) :
                the_post();
		    $initCoords[]=bv_get_geompoint($post);
                // digitalnomad_post_preview_layout function is delivered already escaped
                echo digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            endwhile;
		*/
	    echo "</div>";
	    wp_localize_script('bv-list-stories-script','initCoords',$initCoords);
        else:
            get_template_part( 'templates/post', 'none' );
        endif;
do_action('digitalnomad_preview_layout', false);
