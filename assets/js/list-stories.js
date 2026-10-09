// Scroll-direction flags controlling when new posts are loaded.
//
// These MUST stay on `window`. bv_map/assets/js/main.js writes
// window.load_more_top / window.load_more_bottom directly when a marker is
// clicked, to re-enable loading after it has replaced the story list. If they
// were allowed to fall into the closure below they would become function-scoped,
// bv_map's writes would be ignored, and infinite scroll would silently stop
// resuming after a marker click -- with no error to show for it.
window.load_more_bottom = true;
window.load_more_top = true;

//Varibales for setting scroll-top buffer,
// _loadMoreTopBuffer - calculated buffer used for triggering up-scroll
// _loadTopProcentage - percentage of screen height used for calculation of buffer (hardcoded to 20% of screen height)
var _loadMoreTopBuffer,
    _loadTopProcentage = 20;

//Variable for storing current scrolling position inside .article-main 
var _currentScrollPos;

jQuery( function ( $ ) {
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
	// initCoords is localised by category.php / index.php only, so it is absent
	// on any other template this script happens to load on.
	var postCoords = Array.isArray( window.initCoords ) ? window.initCoords.filter(Boolean) : [];

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
        

                //  Use #load-more-top instead of this
                // console.log( pv.template_path + 'custom-widgets/ajax/ajax.php');
                if(shouldLoad && window.load_more_top){
                    shouldLoad=false;
                    let post_id = $('article.preview_content')[0].getAttribute('id');
                    post_id = post_id.replace('post_','');
                    //console.log(post_id);
                    //console.log(pv.template_path)
                    $.ajax({
                        url: pv.ajax_url,
                        type: "POST",
                        data: {
                            'data'  : post_id,
                            //'action':'load_more_posts',
                            'action':'load_posts',
                            '_ajax_nonce': pv.ajax_nonce,
				'query': bvVarMap.queried_obj,
			    'journey_id' : $('#journey_id').val()??"",
                            'previous': 0
                        },
                        success: function(data){
                            data = JSON.parse(data);
                            if(!data.html){
                                window.load_more_top = false;
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
                if(shouldLoad && window.load_more_bottom){
                    shouldLoad=false;
                    // let post_id = $('.story-post')[0].getAttribute("data-id");
                    let post_id = $('article.preview_content')[$('article.preview_content').length-1].getAttribute('id');
                    post_id = post_id.replace('post_','');
                    // console.log(post_id);
                    // console.log(pv.template_path)

                    $.ajax({
                        url: pv.ajax_url,
                        type: "POST",
                        data: {
                            'data'  : post_id,
                            'action':'load_posts',
                            '_ajax_nonce': pv.ajax_nonce,
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
                            // Was `if(!data)`, which can never be true: data was
                            // JSON.parse'd on the line above, so it is always a
                            // truthy object ({html:"",coords:[]} when exhausted).
                            // load_more_bottom was therefore never set, and the
                            // bottom sentinel stayed in view firing an identical
                            // request forever -- each one booting WordPress.
                            // The top-scroll branch already tests !data.html.
                            if(!data.html){
                                window.load_more_bottom = false;
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
                        };
            }
        if (shouldListen) {
            //    console.log(bvVarMapGeoJson);
            let story_posts = $('[id^="post_"]');
            let elements = [];
            //Finds which element is in the viewport and adds its array to elements variable
            for (let i = 0; i < story_posts.toArray().length; i++) {
                if ($(story_posts[i]).isInViewport()) {
                    elements.unshift(JSON.parse($(story_posts[i]).attr('id').substring(5)));
                }
            }
            // Was `elements[0] !== 'undefined'` -- a comparison against the
            // string literal, which is always true for a number or undefined,
            // so the guard did nothing. When nothing is in the viewport,
            // elements is empty and the code below flew the map to the wrong
            // post.
            if (typeof elements[0] !== 'undefined') {
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
                url: pv.ajax_url,
                type: "POST",
                data: {
                    's'  : val,
                    'action':'load_posts_from_search',
                    '_ajax_nonce': pv.ajax_nonce,
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
