<?php
$path = preg_replace('/wp-content.*$/','',__DIR__); 
include_once($path.'wp-load.php');

if(isset($_POST['fetch_journey_data']) && isset($_POST['journey_id'])){
}

if(isset($_POST['action']) && $_POST['action'] == 'jumpto_post' && isset($_POST['data'])){
	$returnArray = array();
	$current_id = $_POST['data'];
	$post = get_post($current_id);
	$returnArray['html'] = digitalnomad_post_preview_layout_for_ajax();
	$returnArray['coords'] = array(bv_get_geompoint($post));

	$topArray = dn_loadn_preview($_POST['data'],false,3,$_POST['journey_id']);
	if(!empty($topArray['html'])) $returnArray['html'] = $topArray['html'] . $returnArray['html'];
	if(!empty($topArray['coords'])) $returnArray['coords'] = array_merge( $topArray['coords'] , $returnArray['coords']);

	$bottomArray = dn_loadn_preview($_POST['data'],true,3,$_POST['journey_id']);
	if(!empty($bottomArray['html'])) $returnArray['html'] = $returnArray['html'] . $bottomArray['html'];
	if(!empty($bottomArray['coords'])) $returnArray['coords'] = array_merge( $returnArray['coords'] , $bottomArray['coords']);

	echo json_encode($returnArray);
}

if(isset($_POST['action']) && $_POST['action'] == 'load_posts' && isset($_POST['data'])){
	$queried_obj = (object)  $_POST['query'] ;
	$returnArray=dn_loadn_preview($_POST['data'],(bool) $_POST['previous'],7,$_POST['journey_id']);
	echo json_encode($returnArray);
}

if(isset($_POST['action'])&&$_POST['action'] == 'load_posts_from_search' && $_POST['s'] ){
	$args = array( 's' => $_POST['s'],
       			'post_type' => 'post',	);
	if( $_POST['journey_id']){
		$args+= array( 'cat' => $_POST['journey_id']);		
		$args+= array( 'order' => "ASC");		
		/*
        'meta_query'=>array(
            // Add compare to the tags 
            array(
                'key'   => 'bs_search_title',
                'value' => "{$_POST['s']}",
            	'compare' => 'LIKE'
            )
	);
		 */
	}
    $journey = new WP_Query($args);
    // print_r($args);
    $returnArray['html']='';
    $returnArray['coords']=array();
    while($journey->have_posts()){
        $journey->the_post();
        $returnArray['html'].= digitalnomad_post_preview_layout_for_ajax(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        array_push($returnArray['coords'], bv_get_geompoint($post));
    }
    if(!$returnArray['html'])$returnArray['empty']="<h3 class='bs_search_emply'>No posts found</h3>";
    echo json_encode($returnArray);
}
