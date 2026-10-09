// Setting variables for controlling up-scroll/down-scroll used for loading new posts
// Varibales are being reset when user clicks on the map - bv_map/assets/js/main.js
var load_more_bottom = true;
var load_more_top = true;

//Varibales for setting scroll-top buffer,
// _loadMoreTopBuffer - calculated buffer used for triggering up-scroll
// _loadTopProcentage - percentage of screen height used for calculation of buffer (hardcoded to 20% of screen height)
var _loadMoreTopBuffer,
    _loadTopProcentage = 20;

//Variable for storing current scrolling position inside .article-main 
var _currentScrollPos;

$(() => {
    $.fn.isInViewport = function() {
        if (!$(this).length) return false;
        let elementTop = $(this).offset().top;
        let elementBottom = elementTop + $(this).outerHeight();
        let viewportTop = $(window).scrollTop();
        let viewportBottom = viewportTop + $(window).height();
        //return elementTop > viewportTop && elementBottom < viewportBottom;
        return elementBottom > viewportTop && elementTop < viewportBottom;
    };
    var shouldLoad = true;
    var shouldListen = true;
    var lastElement = null;
	var postCoords = initCoords.filter(Boolean);

    function getCoordinatesById(postid) {
        if (!bvVarMapGeoJson) return;
        if (!bvVarMapGeoJson.features) return;
	if (!bvVarMapGeoJson.features.length >0 ) return;
        let featureFound= bvVarMapGeoJson.features.find(({
            id
        }) => id == postid);
        if(featureFound){
		return featureFound.geometry.coordinates;
	}else{
		return bvVarMapGeoJson.features[0].geometry.coordinates;
	}
    }

	function getCoordsById(postid){
		//console.log (postCoords,'in function');
		if (! postCoords) return;
		if (! postCoords.length > 0) return;
		let geom_point = postCoords.find(({id})=> id == postid);
		if (geom_point){
			return geom_point.geometry.coordinates;
		}else{
			return getCoordinatesById(postid);
		}
	}

    var toogleScroll = true;
    //var load_more_bottom = true;
    //var load_more_top = true;
	if ($('#last_post').val()){
		  $("#post_" + $('#last_post').val())[0].scrollIntoView({
		                                 //behavior: "auto", // or "auto" or "instant"
		                                 behavior: "smooth", // or "auto" or "instant"
		                                 block: "center" // or "end"
		  });
	}else if ($('#first_event').val()){
		  $("#post_" + $('#first_event').val())[0].scrollIntoView({
		                                 //behavior: "auto", // or "auto" or "instant"
		                                 behavior: "smooth", // or "auto" or "instant"
		                                 block: "center" // or "end"
		  });
	}
    $('.article-main').scroll(function() {
            //_currentTop - variable for defining current top position before loading data to the top
            //_loadTopHeight - variable for calculating hight of the loaded data to the top
            var _currentTop, 
                _loadTopHeight = 0;

            //calculate bufer limit for up-scroll
            _loadMoreTopBuffer = Math.round(
                $(this).get(0).scrollHeight * (_loadTopProcentage/100)
            );

            //Storing current scrolling position and use it for calculating position when data is loaded at the top
            _currentScrollPos = $(this).scrollTop();
             
            //Check if current scroll position is less then buffer limit
            if($(this).scrollTop() < _loadMoreTopBuffer) {
                // Defining what is the current first child element in the story list
                _currentTop = $(".article-main #journey-story-wrapper .story-list").children().first();
        
                //if(!load_more_top)return;

                //  Use #load-more-top instead of this
                // console.log( pv.template_path + 'custom-widgets/ajax/ajax.php');
                if(shouldLoad && load_more_top){
                    shouldLoad=false;
                    let post_id = $('article.preview_content')[0].getAttribute('id');
                    post_id = post_id.replace('post_','');
                    //console.log(post_id);
                    //console.log(pv.template_path)
                    $.ajax({
                        url: pv.template_path + '/inc/ajax.php',
                        type: "POST",
                        data: {
                            'data'  : post_id,
                            //'action':'load_more_posts',
                            'action':'load_posts',
				'query': bvVarMap.queried_obj,
			    'journey_id' : $('#journey_id').val()??"",
                            'previous': 0
                        },
                        success: function(data){
                            data = JSON.parse(data);
                            if(!data.html){
                                load_more_top = false;
                                //$('#load-more-top ').remove();
                            }else{
                            //console.log(data,  'true-top');
                            	$('#content .story-list').prepend(data.html);
                                postCoords=data.coords.filter(Boolean).concat(postCoords);
                                
                                //Looping through the articles loaded at the top and calculating its height
                                _currentTop.prevAll().each(function() {
                                    _loadTopHeight += $(this).outerHeight();
                                });

                                //Setting scroll top position to the last viewed post before articles are loaded at the top
                                $(".article-main").scrollTop(_loadTopHeight + _currentScrollPos);

                                /*$("#post_" + post_id)[0].scrollIntoView({
                                                                        behavior: "auto", // or "auto" or "instant"
                                                                        block: "center" // or "end"
                                    });*/
                            }
                            shouldLoad = true
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            shouldLoad = true
                            console.log(errorThrown);
                        }
                    })
                }
            } 
            if($('#load-more-bottom') && $('#load-more-bottom').isInViewport()){
                //if(!load_more_bottom)return;
                if(shouldLoad && load_more_bottom){
                    shouldLoad=false;
                    // let post_id = $('.story-post')[0].getAttribute("data-id");
                    let post_id = $('article.preview_content')[$('article.preview_content').length-1].getAttribute('id');
                    post_id = post_id.replace('post_','');
                    // console.log(post_id);
                    // console.log(pv.template_path)

                    $.ajax({
                        url: pv.template_path + '/inc/ajax.php',
                        type: "POST",
                        data: {
                            'data'  : post_id,
                            'action':'load_posts',
                            //'action':'load_more_posts',
				'query': bvVarMap.queried_obj,
			    'journey_id' : $('#journey_id').val()??"",
                            'previous': 1
                        },
                        success: function(data){
                            data = JSON.parse(data);
                            //console.log(data,'false-bottom');
                            //console.log(data.coords,'coords');
                            //console.log(postCoords,'postCoords');
                            if(!data){
                                load_more_bottom = false;
                                // $('#load-more-bottom').remove();
                            }else{
                            	$('#content .story-list').append(data.html);
				postCoords=postCoords.concat(data.coords.filter(Boolean) );
			    }
                            shouldLoad = true
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(errorThrown);
                            shouldLoad = true
                        }
                    });
                    delete post_id;
                };
            }
        if (shouldListen) {
            //    console.log(bvVarMapGeoJson);
            let story_posts = $('[id^="post_"]');
            let elements = [];
            //Finds which element is in the viewport and adds its array to elements variable
            for (i = 0; i < story_posts.toArray().length; i++) {
                if ($(story_posts[i]).isInViewport()) {
                    elements.unshift(JSON.parse($(story_posts[i]).attr('id').substring(5)));
                }
            }
            if (elements[0] !== 'undefined' ) {
                // console.log(elements[0], lastElement);
		//console.log(window.innerHeight ,'Height');
		//console.log(window.innerWidth ,'Width');
                // Searches for the element which has a given ID from the last element (we find it with 0 because unshift was used instead of push )
                if (elements[0] != lastElement) {
                    // mapbox function which flies to the element on the map
			//console.log(getCoordsById(elements[1]) , getCoordsById(elements[0]) , 'coords');
			//console.log(turf.multiPoint([getCoordsById(elements[0]),getCoordsById(elements[1]??elements[0])] ) , ' m p');
			//console.log(turf.bbox(turf.multiPoint([getCoordsById(elements[0]),getCoordsById(elements[1]??elements[0])] ) , ' bbox'));
			//map.fitBounds( turf.bbox(turf.multiPoint([getCoordinatesById(elements[0]),getCoordinatesById(elements[1]??elements[0])] )),
			map.fitBounds( turf.bbox(turf.multiPoint([getCoordsById(elements[0]),getCoordsById(elements[1]??elements[0])] )),
			 {
				 essential: true,
			        padding: Math.round(Math.min( window.innerHeight, window.innerWidth) /7 ) ,
				 easing: function (t) {
					 //return t;
					 return Math.sin( t * Math.PI/2 );
				 },
					        duration: 2000
						    });
			/*
                    map.flyTo({
                        center: getCoordinatesById(elements[0]),
                        zoom: 9
                    });
			*/
                    lastElement = elements[0];
                    
                    window.dispatchEvent(new Event('resize'));
                    toogleScroll = !toogleScroll;
                    shouldListen = false;
                    // It only tries to fly to an element every 1 second, this will help with performance
                    setTimeout(() => {
                        shouldListen = true;
                    }, 1000);
                }
            }
        }
    });
    var update_list_var;
    $("#search-story-wrapper").hide();
    $("#bs-search-input").on('keyup',function(){
        
        // clearTimeout(update_list_var);
        updateSearchList(this.value);
        
    });
    function updateSearchList(val){
        shouldLoad = true;
        clearTimeout(update_list_var);
        if(!val.length){
            $('#journey-story-wrapper').show();
            $('#search-story-wrapper').hide();
            return;
        }
        update_list_var = setTimeout(function(){
            $.ajax({
                url: pv.template_path + '/inc/ajax.php',
                type: "POST",
                data: {
                    's'  : val,
                    'action':'load_posts_from_search',
                    'journey_id' : $('#journey_id').val()??""
                },
                success: function(data){
                    //console.log(data);
                    $('#journey-story-wrapper').hide();
                    $('#search-story-wrapper').show();
                    data = JSON.parse(data);
                    //console.log(data,'search results');

                    $('#search-story-wrapper').html(data.html);
                    if(data.empty){
                        $('#search-story-wrapper').append(data.empty);
                    }
		postCoords=data.coords.filter(Boolean);
                    shouldLoad = true
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                    shouldLoad = true
                }
            }); 
        },1000)
    }

    
})
