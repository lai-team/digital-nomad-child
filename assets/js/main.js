jQuery(document).ready(function(){

    //Get the page url
    var _url  = window.location.href;

    //Check if url contains "webview=1"
    if (_url.indexOf("webview=1") > -1) {
        //Hide language selector in case url contains "webview=1"
        jQuery("#trp-floater-ls-current-language").hide();

        //Hide cookie msg in case url contains "webview=1"
        jQuery("#branda-cookie-notice").hide();
    }

    jQuery(".toggle.search-toggle").click(function(){
        jQuery(".search-modal").addClass("active");
        jQuery("#bs-search-input").focus();
    });
        
    jQuery("#bs-search-input").blur(function() {
        jQuery(".search-modal").removeClass("active");
    });

    jQuery(".toggle.nav-toggle").click(function(){
        jQuery(".menu-modal").addClass("active");
        jQuery("#main").addClass("remove-scroll");
    });
        
    jQuery(".close-nav-toggle").click(function() {
        jQuery(".menu-modal").removeClass("active");
        jQuery("#main").removeClass("remove-scroll");
    });

    jQuery("#main").scroll(function() {
        if(jQuery(this).scrollTop() > jQuery(".header-inner .section-inner").height()) {
            jQuery(".header-inner").addClass("scrolled");
        } else if(jQuery(this).scrollTop() < jQuery(".header-inner .section-inner").height()) {
            jQuery(".header-inner").removeClass("scrolled");
        }
    });

    /**
     * Function for activating/closing sub-category menu
     * 
     * When user clicks on arrow-down btn/icon, sub-category menu is being activated
     * When user clicks on arrow-up btn/icon, all sub-category menus are being closed
     * 
     */
    jQuery("button.toggle.sub-menu-toggle.fill-children-current-color").click(function() {
        //Adds or removes active class from the dropdown btn
        jQuery(this).toggleClass( "active" );
        
        //Gets the parent element of the clicked dropdown btn
        var _this_parent = jQuery(this).parent();

        //Gets the submenu of the clicked dropdown btn and toggles the class active (adds or removes the class)
        _this_parent.next().toggleClass( "active" );
        
        //Check if the sub-menu has active class
        if( !_this_parent.next().hasClass("active")) {

            //Gets all active sub-menu items insed of the current active sub-menu
            var _unset_all_active_submenu = _this_parent.next().find("ul.children.active");

            //Loops through the list of all active sub-menu items
            _unset_all_active_submenu.each(function() {
                
                //Remove the active class from all active sub-menu items
                jQuery(this).removeClass("active");

                //Resets dropdown btn for all active sub-menu items (changes arrow-up to arrow-down)
                jQuery(this).prev().find(".active").each(function () {
                    jQuery(this).removeClass("active");
                });
            });
        }
    });
});

function expandJourney(ob){
    let journeyDiv = $(ob.parentElement);
    journeyDiv.find('.stories-wrapper').toggleClass('hide');
    $(ob).text($(ob).text() == "Hide"?"Expand":"Hide");
    // $.ajax({
    //     url : pv.template_path + '/inc/ajax.php',
    //     type : 'post',
    //     data: {
    //         journey_id : id,
    //         fetch_journey_data : true,
    //     },
    //     success: function(data){
    //         $(data).insertAfter(journeyDiv);
    //         console.log(data);
    //         ob.remove();
    //     },
    //     error: function(err){
    //         console.log(err);
    //     }
    // })
}
$(() => {
    $('.stories-wrapper').addClass('hide');
    if ($("#map").length) {
        $("#main").addClass("article-main");
    }

    
    
})
