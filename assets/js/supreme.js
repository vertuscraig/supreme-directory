jQuery(document).ready(function () {



    if ( jQuery( "a.sd-my-account-link" ).length ) {
        jQuery('a.sd-my-account-link').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery('.sd-my-account-dd').toggle();
        });

        jQuery(document).click(function (e) {
            if (e.target.class != 'sd-my-account-dd' && !jQuery('.sd-my-account-dd').find(e.target).length) {
                jQuery('.sd-my-account-dd').hide();
            }
        });
    }

    jQuery("body").on("geodir_setup_search_form", function(){
        if (jQuery(".featured-area .geodir-cat-list-tax").length) {
            var postType = jQuery('.featured-area .search_by_post').val();
            jQuery(".geodir-cat-list-tax").val(postType);
            jQuery(".geodir-cat-list-tax").change();
        }
    });

    jQuery("#showMap").click(function () {
        jQuery('body').addClass('sd-map-only').removeClass('sd-listings-only');
        jQuery( "#hideMap" ).appendTo( ".gd_listing_map_TopLeft" );

    });

    jQuery("#hideMap").click(function () {
        jQuery('body').addClass('sd-listings-only').removeClass('sd-map-only');
        jQuery( "#hideMap" ).appendTo( ".sd-mobile-search-controls" );

    });

    jQuery("#showSearch").click(function () {
        jQuery("body").toggleClass('sd-show-search');

        if ( typeof geodir_reposition_compass == 'function' ) {
                    geodir_reposition_compass();
        }

    });
    

    if ( jQuery( ".sd-detail-cta a.dt-btn" ).length ) {
        jQuery(".sd-detail-cta a.dt-btn").click(function () {
            sd_scroll_to_reviews();
        });
    }

    if ( jQuery( ".sd-ratings a.geodir-pcomments" ).length ) {
        jQuery(".sd-ratings a.geodir-pcomments").click(function () {
            sd_scroll_to_reviews();
        });
    }

    // fire any resize functions
    jQuery(window).on('resize', function(){
        // var win = $(this); //this = window
        // if (win.height() >= 820) { /* ... */ }
        // if (win.width() >= 1280) { /* ... */ }
        console.log('resized');
        sd_archive_container_max_height();
    });

});

function sd_archive_container_max_height(){
    if ( jQuery( "body.geodir-fixed-archive" ).length) {
        $offsetHeight = jQuery('.sd-container .container .entry-content').offset().top;
        $maxHeight = window.innerHeight - $offsetHeight;
        //alert($maxHeight);
        jQuery('body.geodir-fixed-archive .sd-container .container .entry-content').css('max-height',$maxHeight);
        jQuery('.main_map_wrapper, #gd_map_canvas_archive, #gd_map_canvas_archive_loading_div').css('height',$maxHeight);

    }
}

function sd_scroll_to_reviews(){
    jQuery('.geodir-tab-head [data-tab="#reviews"]').closest('dd').trigger('click');
    setTimeout(function(){jQuery('html,body').animate({scrollTop:jQuery('#respond').offset().top}, 'slow');console.log('scroll')}, 200);
}

var $sd_sidebar_position = '';

(function(){

    // set the sidebar position var
    if(jQuery('body.sd-right-sidebar').length){
        $sd_sidebar_position = 'right';
    }else{
        $sd_sidebar_position = 'left';
    }

    if ( jQuery( ".featured-img" ).length ) {

        var windowHeight = screen.height;


        var parallax = document.querySelectorAll(".featured-img"),
            speed = 0.6;
        var bPos = jQuery( ".featured-img").css("background-position");
        var arrBpos= bPos.split(' ');
        var originalBpos = arrBpos[1];
        var fetHeight = parseInt(jQuery( ".featured-area").css("height"));
        var fetAreHeight = jQuery( ".featured-area").offset().top + fetHeight;


        window.onscroll = function () {
            var f =0;
            [].slice.call(parallax).forEach(function (el, i) {
                if(f>1){return;}
                var windowYOffset = window.pageYOffset;

                originalBpos = parseInt(originalBpos);

                var perc =  windowYOffset / fetAreHeight + (originalBpos / 100);

                //"50% calc("+originalBpos+" - " + (windowYOffset * speed) + "px)"

                parallaxPercent = 100*perc;
                if(parallaxPercent>100){parallaxPercent=100;}

                jQuery(el).css("background-position","50% "+parallaxPercent+"%" );
                f++;

            });
        };
    }

    jQuery("#sd-home-scroll").click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({
            scrollTop: jQuery(".featured-area").outerHeight()
        }, 1000);
    });


    sd_insert_archive_resizer();


})();



// insert archive page size adjuster
function sd_insert_archive_resizer(){
    $screen_width = screen.width;
    if(jQuery('body.geodir-fixed-archive .sd-container .container').length &&  $screen_width > 992){
        jQuery('body.geodir-fixed-archive .sd-container .container').append('<button class="sd-archive-resizer"><i class="fas fa-arrows-alt-h"></i></button>');
        sd_position_archive_resizer();
    }
}

function sd_position_archive_resizer(){

    var $container = '.entry-content';
    var $offset = 21;
    if($sd_sidebar_position=='left') {
        $container = '.sd-sidebar';
        $offset = 13;
    }

        $width = jQuery('body.geodir-fixed-archive .sd-container .container '+$container).outerWidth() - $offset;
    jQuery('.sd-archive-resizer').css('left',$width);
}
var $sd_set_archive_width = false;
// function to adjust width of archive elements
jQuery('body.geodir-fixed-archive .sd-archive-resizer').mousedown(function(e){
    e.preventDefault();

    var $container = '.entry-content';
    if($sd_sidebar_position=='left') {
        $container = '.sd-sidebar';
    }

    jQuery(document).mousemove(function(e){

        jQuery('.container '+$container).css("width",e.pageX+2);
        sd_position_archive_resizer();
        $sd_set_archive_width = true;
    });
});
jQuery(document).mouseup(function(e){
    jQuery(document).unbind('mousemove');
    // set the value if we have localstorage
    if($sd_set_archive_width && geodir_is_localstorage()){

        var $container = '.entry-content';
        var $offset = 21;
        if($sd_sidebar_position=='left') {
            $container = '.sd-sidebar';
            $offset = 13;
        }

        $width = jQuery('body.geodir-fixed-archive .sd-container .container '+$container).outerWidth() - $offset;
        localStorage.setItem('sd_archive_width', $width);
        window.dispatchEvent(new Event('resize'));// so map tiles fill in
    }
});


