<?php
/**
 * Functions for the GeoDirectory plugin if installed
 *
 * @since 1.0.0
 * @package Supreme_Directory
 */

/*
 * remove breadcrumb from search, listings and detail page.
 */
remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);
remove_action('geodir_author_before_main_content', 'geodir_breadcrumb', 20);

/*
 * add search widget on top of search results and in listings page.
 */
add_action('geodir_search_content', 'sd_search_form_on_search_page', 4);
add_action('geodir_listings_content', 'sd_search_form_on_search_page', 4);


/**
 * Outputs the search widget.
 *
 * @since 1.0.0
 */
function sd_search_form_on_search_page()
{
    echo do_shortcode('[gd_advanced_search]');
}


/**
 * Add body classes to the HTML where needed.
 *
 * @since 0.0.1
 * @param array $classes The array of body classes.
 * @return array The array of body classes.
 */
function sd_custom_body_class_gd($classes)
{
    if (geodir_is_page('location')) {
        $classes[] = 'sd-location';
    } elseif (geodir_is_page('preview')) {
        $classes[] = 'sd-preview';
    } elseif (geodir_is_page('listing')) {
        if (get_option('geodir_show_listing_right_section', true)) {
            $classes[] = 'sd-right-sidebar';
        } else {
            $classes[] = 'sd-left-sidebar';
        }
    } elseif (geodir_is_page('add-listing')) {
        $classes[] = 'sd-add';
    }
    // return the modified $classes array
    return $classes;
}

add_filter('body_class', 'sd_custom_body_class_gd');


/**
 * Remove and change some standard GeoDirectory widget areas.
 *
 * This function disables the listings pages sidebars and uses the GeoDirectory design setting to select map left/right on listings pages.
 *
 * @since 1.0.0
 */
function sd_theme_actions()
{
    unregister_sidebar('geodir_listing_left_sidebar');
    unregister_sidebar('geodir_listing_right_sidebar');

    unregister_sidebar('geodir_search_left_sidebar');
    unregister_sidebar('geodir_search_right_sidebar');

    unregister_sidebar('geodir_author_left_sidebar');
    unregister_sidebar('geodir_author_right_sidebar');

    // listings page
    if (get_option('geodir_show_listing_right_section', true)) {
        add_action('geodir_listings_sidebar_right_inside', 'sd_map_show');
        remove_action('geodir_listings_sidebar_left', 'geodir_action_listings_sidebar_left', 10);
    } else {
        add_action('geodir_listings_sidebar_left_inside', 'sd_map_show');
        remove_action('geodir_listings_sidebar_right', 'geodir_action_listings_sidebar_right', 10);
    }

    // search page
    if (get_option('geodir_show_search_right_section', true)) {
        add_action('geodir_search_sidebar_right_inside', 'sd_map_show');
        remove_action('geodir_search_sidebar_left', 'geodir_action_search_sidebar_left', 10);
    } else {
        add_action('geodir_search_sidebar_left_inside', 'sd_map_show');
        remove_action('geodir_search_sidebar_right', 'geodir_action_search_sidebar_right', 10);
    }

    // author page
    if (get_option('geodir_show_author_right_section', true)) {
        add_action('geodir_author_sidebar_right_inside', 'sd_map_show');
        remove_action('geodir_author_sidebar_left', 'geodir_action_author_sidebar_left', 10);
    } else {
        add_action('geodir_author_sidebar_left_inside', 'sd_map_show');
        remove_action('geodir_author_sidebar_right', 'geodir_action_author_sidebar_right', 10);
    }

}

add_action('widgets_init', 'sd_theme_actions', 15);


/**
 * Output the listing map widget.
 *
 * @since 1.0.0
 */
function sd_map_show()
{
    echo do_shortcode('[gd_listing_map width=100% autozoom=true]');
}


/**
 * Output the mobile map buttons HTML.
 *
 * @since 1.0.0
 */
function sd_mobile_map_buttons()
{
    echo '<div class="sd-mobile-search-controls">
			<a class="dt-btn" id="showSearch" href="#">
				<i class="fa fa-search"></i> ' . __('SEARCH LISTINGS', 'supreme-directory') . '</a>
			<a class="dt-btn" id="hideMap" href="#"><i class="fa fa-th-large">
				</i> ' . __('SHOW LISTINGS', 'supreme-directory') . '</a>
			<a class="dt-btn" id="showMap" href="#"><i class="fa fa-map-o">
				</i> ' . __('SHOW MAP', 'supreme-directory') . '</a>
			</div>';
}

add_action('geodir_listings_content', 'sd_mobile_map_buttons', 5);
add_action('geodir_search_content', 'sd_mobile_map_buttons', 5);


/*################################
      DETAIL PAGE FUNCTIONS
##################################*/

// remove the preview page code to move it inside the featured area
remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_preview_code', 9);


add_action('sd_details_featured_area_text','sd_add_event_dates_featured_area');
function sd_add_event_dates_featured_area(){
    global $post,$geodir_date_format,$geodir_date_time_format;
    ?>

    <div class="header-wrap sd-event-dates-head">
        <?php

        if(isset($post->recurring_dates)){
            $recuring_data = maybe_unserialize( $post->recurring_dates );
//print_r($recuring_data);
            if ( !empty( $recuring_data ) && ( isset( $recuring_data['event_recurring_dates'] ) && $recuring_data['event_recurring_dates'] != '' ) || ( isset( $post->is_recurring ) && !empty( $post->is_recurring ) ) ) {
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                $geodir_num_dates = 0;
                $starttimes = '';
                $endtimes = '';
                $astarttimes = array();
                $aendtimes = array();
                $output = '';
                // Check recurring enabled
                $recurring_pkg = geodir_event_recurring_pkg( $post );

                $hide_past_dates = true;

                if ( !isset( $recuring_data['repeat_type'] ) ) {
                    $recuring_data['repeat_type'] = 'custom';
                }

                $repeat_type = isset( $recuring_data['repeat_type'] ) && in_array( $recuring_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $recuring_data['repeat_type'] : 'year'; // day, week, month, year, custom

                $different_times = isset( $recuring_data['different_times'] ) && !empty( $recuring_data['different_times'] ) ? true : false;

                if ( $repeat_type == 'custom' && $different_times ) {
                    $astarttimes = isset( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
                    $aendtimes = isset( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
                } else {
                    $starttimes = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                    $endtimes = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                }

                if(isset($_REQUEST['gde']) && $_REQUEST['gde']){
                    //print_r($event_recurring_dates);
                    if(in_array($_REQUEST['gde'],$event_recurring_dates)){
                        $event_recurring_dates = array(esc_html($_REQUEST['gde']));
                    }
                }

                foreach( $event_recurring_dates as $key => $date ) {
                    $geodir_num_dates++;

                    if ( $repeat_type == 'custom' && $different_times ) {
                        if ( !empty( $astarttimes ) && isset( $astarttimes[$key] ) ) {
                            $starttimes = $astarttimes[$key];
                            $endtimes = $aendtimes[$key];
                        } else {
                            $starttimes = '';
                            $endtimes = '';
                        }
                    }

                    $duration = isset( $recuring_data['duration_x'] ) && (int)$recuring_data['duration_x'] > 0 ? (int)$recuring_data['duration_x'] : 1;
                    $duration--;
                    $enddate = date_i18n( 'Y-m-d', strtotime( $date . ' + ' . $duration . ' day' ) );

                    // Hide past dates
                    if ( $hide_past_dates && strtotime( $enddate ) < strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ) ) {
                        $geodir_num_dates--;
                        continue;
                    }

                    $sdate = strtotime( $date . ' ' . $starttimes );
                    $edate = strtotime( $enddate . ' ' . $endtimes );

                    $start_date = date_i18n( $geodir_date_time_format, $sdate );
                    $end_date = date_i18n( $geodir_date_time_format, $edate );

                    $full_day = false;
                    $same_datetime = false;

                    if ( $starttimes == $endtimes && ( $starttimes == '' || $starttimes == '00:00:00' || $starttimes == '00:00' ) ) {
                        $full_day = true;
                    }

                    if ( $start_date == $end_date && $full_day ) {
                        $same_datetime = true;
                    }

                    $link_date = date_i18n( 'Y-m-d', $sdate );
                    $title_date = date_i18n( $geodir_date_format, $sdate );
                    if ( $full_day ) {
                        $start_date = $title_date;
                        $end_date = date_i18n( $geodir_date_format, $edate );
                    }



                    $recurring_class = 'gde-recurr-link';
                    $recurring_class_cont = 'gde-recurring-cont';
                    if ( isset( $_REQUEST['gde'] ) && $_REQUEST['gde'] == $link_date ) {
                        $recurring_event_link = 'javascript:void(0);';
                        $recurring_class .= ' gde-recurr-act';
                        $recurring_class_cont .= ' gde-recurr-cont-act';
                    }

                    $output .= '<p class="' . $recurring_class_cont . '">';
                    $output .= '<i class="fa fa-caret-right"></i> ' . $start_date;
                    if ( !$same_datetime ) {
                        $output .= '<br />';
                        $output .= '<i class="fa fa-caret-left"></i> ' . $end_date;
                    }
                    $output .= '</p>';
                    if($geodir_num_dates>0){break;}
                }
            }

            echo $output;

        }

        ?>
    </div>
    <?php


}



add_action('geodir_wrapper_open', 'sup_add_feat_img_head', 4, 1);

//remove title from listing detail page
remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);
//remove slider from listing detail page
remove_action('geodir_details_main_content', 'geodir_action_details_slider', 30);



/**
 * Remove details info from sidebar.
 *
 * @since 1.0.0
 * @return array
 */
function my_change_sidebar_content_order($arr)
{

    $arr = array_diff($arr, array('geodir_social_sharing_buttons','geodir_share_this_button','geodir_detail_page_review_rating'));

    return $arr;
}

add_filter('geodir_detail_page_sidebar_content', 'my_change_sidebar_content_order',10,1);

// Remove taxonomies from detail page content
remove_action('geodir_details_main_content', 'geodir_action_details_taxonomies', 40);




/**
 * Output the listings images as a gallery.
 *
 * Used to add the listins images to the sidebar.
 *
 * @since 1.0.0
 */
function sd_img_gallery_output()
{
    $excluded_tabs = get_option('geodir_detail_page_tabs_excluded',true);
    if(is_array($excluded_tabs) && in_array('post_images',$excluded_tabs)){
        global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;

        $post_id = !empty($post) && isset($post->ID) ? (int)$post->ID : 0;
        $request_post_id = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;
        $is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend

        if ($is_backend_preview && !$post_id > 0 && $request_post_id > 0) {
            $post = geodir_get_post_info($request_post_id);
            setup_postdata($post);
        }

        $geodir_post_detail_fields = geodir_show_listing_info('detail');

        $thumb_image = '';

        if (geodir_is_page('detail')) {

            $post_images = geodir_get_images($post->ID, 'thumbnail');
            if (!empty($post_images)) {
                foreach ($post_images as $image) {
                    $thumb_image .= '<a href="' . $image->src . '">';
                    $thumb_image .= geodir_show_image($image, 'thumbnail', true, false);
                    $thumb_image .= '</a>';
                }
            }

        } elseif (geodir_is_page('preview')) {

            if (isset($post->post_images))
                $post->post_images = trim($post->post_images, ",");

            if (isset($post->post_images) && !empty($post->post_images))
                $post_images = explode(",", $post->post_images);

            if (!empty($post_images)) {
                foreach ($post_images as $image) {
                    if ($image != '') {
                        $thumb_image .= '<a href="' . $image . '">';
                        $thumb_image .= geodir_show_image(array('src' => $image), 'thumbnail', true, false);
                        $thumb_image .= '</a>';
                    }
                }
            }

        }

        ?>
        <?php if (geodir_is_page('detail') || geodir_is_page('preview')) { ?>
            <div id="geodir-post-gallery" class="clearfix"><?php echo $thumb_image; ?></div>
        <?php }
    }
}

add_action('geodir_detail_sidebar_inside', 'sd_img_gallery_output', 1);

// add recurring dates to sidebar if events installed
if(function_exists('geodir_event_show_shedule_date')){
    add_action('geodir_detail_sidebar_inside', 'geodir_event_show_shedule_date', '1.5');
}

/**
 * Output the details page map HTML.
 *
 * @since 1.0.0
 */
function sd_map_in_detail_page_sidebar()
{

    $excluded_tabs = get_option('geodir_detail_page_tabs_excluded',true);
    if(is_array($excluded_tabs) && in_array('post_map',$excluded_tabs)){
        global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;

        $post_id = !empty($post) && isset($post->ID) ? (int)$post->ID : 0;
        $request_post_id = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;
        $is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend

        if ($is_backend_preview && !$post_id > 0 && $request_post_id > 0) {
            $post = geodir_get_post_info($request_post_id);
            setup_postdata($post);
        }

        if(!isset($post->post_latitude) || $post->post_latitude=''){
            return '';// if not address, bail.
        }
        $geodir_post_detail_fields = geodir_show_listing_info('detail');

        if (geodir_is_page('detail')) {

            $map_args = array();
            $map_args['map_canvas_name'] = 'detail_page_map_canvas';
            $map_args['width'] = '300';
            $map_args['height'] = '400';
            if ($post->post_mapzoom) {
                $map_args['zoom'] = '' . $post->post_mapzoom . '';
            }
            $map_args['autozoom'] = false;
            $map_args['child_collapse'] = '0';
            $map_args['enable_cat_filters'] = false;
            $map_args['enable_text_search'] = false;
            $map_args['enable_post_type_filters'] = false;
            $map_args['enable_location_filters'] = false;
            $map_args['enable_jason_on_load'] = true;
            $map_args['enable_map_direction'] = true;
            $map_args['map_class_name'] = 'geodir-map-detail-page';

        } elseif (geodir_is_page('preview')) {

            global $map_jason;
            $map_jason[] = $post->marker_json;

            $address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
            $address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
            $mapview = isset($post->post_mapview) ? $post->post_mapview : '';
            $mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
            if (!$mapzoom) {
                $mapzoom = 12;
            }

            $map_args = array();
            $map_args['map_canvas_name'] = 'preview_map_canvas';
            $map_args['width'] = '300';
            $map_args['height'] = '400';
            $map_args['child_collapse'] = '0';
            $map_args['maptype'] = $mapview;
            $map_args['autozoom'] = false;
            $map_args['zoom'] = "$mapzoom";
            $map_args['latitude'] = $address_latitude;
            $map_args['longitude'] = $address_longitude;
            $map_args['enable_cat_filters'] = false;
            $map_args['enable_text_search'] = false;
            $map_args['enable_post_type_filters'] = false;
            $map_args['enable_location_filters'] = false;
            $map_args['enable_jason_on_load'] = true;
            $map_args['enable_map_direction'] = true;
            $map_args['map_class_name'] = 'geodir-map-preview-page';

        }
        if (geodir_is_page('detail') || geodir_is_page('preview')) { ?>
            <div class="sd-map-in-sidebar-detail"><?php geodir_draw_map($map_args); ?>

            </div>
        <?php }
    }
}

add_action('geodir_detail_sidebar_inside', 'sd_map_in_detail_page_sidebar', 2);


/**
 * Fire the signup functions from GeoDirectory so the SD login form works.
 *
 * @since 1.0.0
 */
function sd_header_login_handler()
{
    if (!geodir_is_page('login') && isset($_REQUEST['log'])) {
        geodir_user_signup();
    }
}

add_action('init', 'sd_header_login_handler');

// add paging html to top of listings
add_action('geodir_before_listing', 'geodir_pagination', 100);

/**
 * Add fav html to listing page image.
 *
 * @since 1.0.0
 * @param object $post The post object.
 */
function sd_listing_img_fav($post)
{
    if (isset($post->ID)) {
        geodir_favourite_html($post->post_author, $post->ID);
    }
}

add_action('geodir_after_badge_on_image', 'sd_listing_img_fav', 10, 1);


// remove pinpoint and normal fav html from listings
remove_action('geodir_after_favorite_html', 'geodir_output_favourite_html_listings', 1);
remove_action('geodir_listing_after_pinpoint', 'geodir_output_pinpoint_html_listings', 1);


// hide toolbar in frontend
// add_filter('show_admin_bar', '__return_false'); // not allowed if submitting to wp.org

// remove core term description from listins pages
remove_action('geodir_listings_page_description', 'geodir_action_listings_description', 10);
add_action('geodir_listings_content', 'geodir_action_listings_description', 2);
// remove location manager term description from listings pages
remove_action('wp_print_scripts', 'geodir_location_remove_action_listings_description', 100);
remove_action('wp_print_scripts', 'geodir_location_remove_action_listings_description', 100);


/*
 * If location manager not installed then display the default location.
 */
if (!function_exists('geodir_current_loc_shortcode')) {
    add_shortcode('gd_current_location_name', 'sd_geodir_current_loc_shortcode');
}


/**
 * Return the default location name.
 *
 * @since 1.0.0
 * @return string The default location.
 */
function sd_geodir_current_loc_shortcode()
{
    global $gd_session;
    $output = geodir_get_default_location();

    $output = $output->city;

    if (($gd_session->get('my_location') || ($gd_session->get('user_lat') && $gd_session->get('user_lon')))) {
        $output = __('Near Me', 'supreme-directory');
    }

    return $output;
}

/*
 * Move listings page title into the main wrapper content.
 */
// move page titles
remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
add_action('geodir_listings_content', 'geodir_action_listings_title', 1);
// search page tile
remove_action('geodir_search_page_title', 'geodir_action_search_page_title', 10);
add_action('geodir_search_content', 'geodir_action_search_page_title', 1);
// author page tile
remove_action('geodir_author_page_title', 'geodir_action_author_page_title', 10);
add_action('geodir_author_content', 'geodir_action_author_page_title', 1);


/**
 * Return the font awesome cog icon HTML.
 *
 * Replace advanced search button with fontawesome cog.
 *
 * @since 1.0.0
 * @return string The font awesome cog sign.
 */
function sd_gd_adv_search_btn_value()
{
    return "&#xf013;";
}

add_filter('gd_adv_search_btn_value', 'sd_gd_adv_search_btn_value', 10);

/**
 * Return the font awesome search icon HTML.
 *
 * Replace advanced search button with fontawesome cog.
 *
 * @since 1.0.0
 * @return string The font awesome cog sign.
 */
function sd_gd_adv_search_s_btn_value()
{
    return "&#xf002;";
}

add_filter('geodir_search_default_search_button_text', 'sd_gd_adv_search_s_btn_value', 10);


function sd_theme_deactivation($newname, $newtheme) {
    // undo set the details page to use list and not tabs
    update_option('geodir_disable_tabs', '0');
    // undo disable some details page tabs that we show in the sidebar
    update_option('geodir_detail_page_tabs_excluded', array());
    // undo Set the installed flag
    update_option('sd-installed', false);

}
add_action("switch_theme", "sd_theme_deactivation", 10 , 2);



//remove send to friend/enquiry from details page
add_filter("geodir_show_geodir_email", '__return_false');
remove_action('geodir_after_detail_page_more_info', 'geodir_payment_sidebar_show_send_to_friend', 11);

function sd_detail_display_notices() {
    if (geodir_is_page('detail')) {
        if (isset($_GET['geodir_claim_request']) && $_GET['geodir_claim_request'] == 'success') {
            ?>
            <div class="alert alert-success" style="text-align: center">
                <?php echo CLAIM_LISTING_SUCCESS; ?>
            </div>
            <?php
        }

        if (isset($_GET['send_inquiry']) && $_GET['send_inquiry'] == 'success') {
            ?>
            <div class="alert alert-success" style="text-align: center">
                <?php echo SEND_INQUIRY_SUCCESS; ?>
            </div>
            <?php
        }

        if (isset($_GET['sendtofrnd']) && $_GET['sendtofrnd'] == 'success') {
            ?>
            <div class="alert alert-success" style="text-align: center">
                <?php echo SEND_FRIEND_SUCCESS; ?>
            </div>
            <?php
        }
    }
}
add_action('sd-detail-details-before', 'sd_detail_display_notices');

//usage editor: [gd_claim_link class="" icon="false"]
//usage php: echo do_shortcode('[gd_claim_link class="" icon="false"]');
function geodir_claim_link_sc($atts) {
    if (function_exists('geodir_load_translation_geodirclaim')) {
        global $post, $preview;

        $defaults = array(
            'class' => 'supreme-btn supreme-btn-small supreme-edit-btn',
            'icon' => "true",
            'link_text' => __('Claim', 'supreme-directory')
        );
        $params = shortcode_atts($defaults, $atts);

        ob_start();

        $geodir_post_type = array();
        if (get_option('geodir_post_types_claim_listing'))
            $geodir_post_type = get_option('geodir_post_types_claim_listing');
        $posttype = (isset($post->post_type)) ? $post->post_type : '';
        if (in_array($posttype, $geodir_post_type) && !$preview) {
            $is_owned = geodir_get_post_meta($post->ID, 'claimed', true);
            if (get_option('geodir_claim_enable') == 'yes' && $is_owned == '0') {

                if (is_user_logged_in()) {

                    echo '<div class="geodir-company_info" style="border: none;margin: 0;padding: 0">';
                    echo '<div class="geodir_display_claim_popup_forms"></div>';
                    echo '<a href="javascript:void(0);" class="'.$params['class'].' geodir_claim_enable">';
                    if ($params['icon'] == 'true') {
                        echo '<i class="fa fa-question-circle"></i>';
                    }
                    echo $params['link_text'];
                    echo '</a>';
                    echo '</div>';
                    echo '<input type="hidden" name="geodir_claim_popup_post_id" value="' . $post->ID . '" />';

                } else {

                    $site_login_url = geodir_login_url();
                    echo '<a href="' . $site_login_url . '" class="'.$params['class'].'">';
                    if ($params['icon'] == 'true') {
                        echo '<i class="fa fa-question-circle"></i>';
                    }
                    echo $params['link_text'];
                    echo '</a>';

                }
            }
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}
add_shortcode('gd_claim_link', 'geodir_claim_link_sc');




/**
 * Output the header featured area image HTML.
 *
 * Add featured banner and listing details above wrapper.
 *
 * @since 1.0.0
 * @param string $page The GeoDirectory page being called.
 */
function sup_add_feat_img_head($page)
{
    if ($page == 'details-page') {

        global $preview, $post;
        $default_img_url = SD_DEFAULT_FEATURED_IMAGE;
        if ($preview) {
            geodir_action_geodir_set_preview_post();//Set the $post value if previewing a post.
            $post_images = array();
            if (isset($post->post_images) && !empty($post->post_images)) {
                $post->post_images = trim($post->post_images, ",");
                $post_images = explode(",", $post->post_images);
            }
            $full_image_url = (isset($post_images[0])) ? $post_images[0] : $default_img_url;
        } else {
            if (has_post_thumbnail()) {
                $full_image_urls = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $full_image_url = $full_image_urls[0];
            } else {
                $full_image_url = $default_img_url;
            }
        }

        ?>
        <div class="featured-area">

            <div class="featured-img" style="background-image: url(<?php echo $full_image_url; ?>);"></div>

            <?php if ($preview) {
                echo geodir_action_geodir_preview_code();
            }else{
            do_action('sd_details_featured_area_text');
            }


             ?>
        </div>
        <?php
        $user_id = get_current_user_id();
        $post_avgratings = geodir_get_post_rating($post->ID);
        $post_ratings = geodir_get_rating_stars($post_avgratings, $post->ID);
        ob_start();
        if (!$preview) {
            geodir_comments_number($post->rating_count);
        } else {

        }
        $n_comments = ob_get_clean();
        if (!$preview) {
            $author_name = get_the_author();
            $entry_author = get_avatar(get_the_author_meta('email'), 100);
            $author_link = get_author_posts_url(get_the_author_meta('ID'));
            $post_type = $post->post_type;
            $post_tax = $post_type . "category";
            $post_cats = $post->{$post_tax};
        } else {
            $author_name = get_the_author_meta('display_name', $user_id);
            $entry_author = get_avatar(get_the_author_meta('email', $user_id), 100);
            $author_link = get_author_posts_url($user_id);
            $post_type = $post->listing_type;
            $post_tax = $post_type . "category";
            $post_cats = isset($post->post_category) ? $post->post_category[$post_tax] : $post->{$post_tax};
        }

        $author_name = apply_filters('sd_detail_author_name', $author_name);
        $entry_author = apply_filters('sd_detail_entry_author', $entry_author);
        $author_link = apply_filters('sd_detail_author_link', $author_link);

        $postlink = get_permalink(geodir_add_listing_page_id());
        $editlink = geodir_getlink($postlink, array('pid' => $post->ID), false);
        $cats_arr = array_filter(explode(",", $post_cats));
        $cat_icons = geodir_get_term_icon();
        ?>
        <?php do_action('sd-detail-details-before'); ?>
        <div class="sd-detail-details">
        <div class="container">
            <div class="sd-detail-author">
                <?php
                if (!$preview && function_exists('geodir_load_translation_geodirclaim')) {
                    $is_owned = geodir_get_post_meta($post->ID, 'claimed', true);
                    if ($is_owned == '1') {
                        ?>
                        <span class="fa fa-stack sd-verified-badge"
                              title="<?php _e('Verified Owner', 'supreme-directory'); ?>">
						<i class="fa fa-circle fa-inverse"></i>
						<i class="fa fa-check-circle"></i>
					</span>
                    <?php
                    }else{
                    $author_link = '#';
                    $author_name = __('Claim Me', 'supreme-directory');
                    $entry_author = '<img src="'.get_stylesheet_directory_uri() . "/images/gravatar2.png".'"  height="100" width="100">';
                    }
                }

                printf('<div class="author-avatar"><a href="%s">%s</a></div>', $author_link, $entry_author);
                printf('<div class="author-link"><a href="%s">%s</a></div>', $author_link, $author_name);

                if (is_user_logged_in() && geodir_listing_belong_to_current_user() ) {
                    ?>
                    <a href="<?php echo $editlink; ?>" class="supreme-btn supreme-btn-small supreme-edit-btn"><i
                            class="fa fa-edit"></i> <?php echo __('Edit', 'supreme-directory'); ?></a>
                <?php }

                if (function_exists('geodir_load_translation_geodirclaim')) {
                    $geodir_post_type = array();
                    if (get_option('geodir_post_types_claim_listing'))
                        $geodir_post_type = get_option('geodir_post_types_claim_listing');
                    $posttype = (isset($post->post_type)) ? $post->post_type : '';
                    if (in_array($posttype, $geodir_post_type) && !$preview) {
                        $is_owned = geodir_get_post_meta($post->ID, 'claimed', true);
                        if (get_option('geodir_claim_enable') == 'yes' && $is_owned != '1') {

                            if (is_user_logged_in()) {

                                echo '<div class="geodir-company_info">';
                                echo '<div class="geodir_display_claim_popup_forms"></div>';
                                echo '<a href="javascript:void(0);" class="supreme-btn supreme-btn-small supreme-edit-btn geodir_claim_enable"><i class="fa fa-question-circle"></i> ' . __('Claim', 'supreme-directory') . '</a>';
                                echo '</div>';
                                echo '<input type="hidden" name="geodir_claim_popup_post_id" value="' . $post->ID . '" />';
                                if (!empty($_REQUEST['gd_go']) && $_REQUEST['gd_go'] == 'claim' && !isset($_REQUEST['geodir_claim_request'])) {
					                echo '<script type="text/javascript">jQuery(function(){jQuery(".supreme-btn.geodir_claim_enable").trigger("click");});</script>';
				                }
                            } else {
                            $current_url = remove_query_arg(array('gd_go'), geodir_curPageURL());
				            $current_url = add_query_arg(array('gd_go' => 'claim'), $current_url);
				            $login_to_claim_url = geodir_login_url(array('redirect_to' => urlencode_deep($current_url)));
				            $login_to_claim_url = apply_filters('geodir_claim_login_to_claim_url', $login_to_claim_url, $post->ID);

                                $site_login_url = $login_to_claim_url;
                                echo '<a href="' . $site_login_url . '" class="supreme-btn supreme-btn-small supreme-edit-btn"><i class="fa fa-question-circle"></i> ' . __('Claim', 'supreme-directory') . '</a>';

                            }
                        }
                    }
                }
                ?>
            </div>
            <!-- sd-detail-suthor end -->
            <div class="sd-detail-info">
                <?php
                echo '<h1 class="sd-entry-title">' . get_the_title();
                ?>
                <?php
                echo '</h1>';
                $sd_address = '<div class="sd-address">';
                if (isset($post->post_city) && $post->post_city) {
                    $sd_address .= $post->post_city;
                }
                if (isset($post->post_region) && $post->post_region) {
                    $sd_address .= ', ' . $post->post_region;
                }
                if (isset($post->post_country) && $post->post_country) {
                    $sd_address .= ', ' . $post->post_country;
                }
                $sd_address .= '</div>';
                echo $sd_address;
                echo '<div class="sd-ratings">' . $post_ratings . ' - <a href="' . get_comments_link() . '" class="geodir-pcomments">' . $n_comments . '</a></div>';
                echo '<div class="sd-contacts">';
                if (isset($post->geodir_website) && $post->geodir_website) {
                    echo '<a href="' . $post->geodir_website . '"><i class="fa fa-external-link-square"></i></a>';
                }
                if (isset($post->geodir_facebook) && $post->geodir_facebook) {
                    echo '<a href="' . $post->geodir_facebook . '"><i class="fa fa-facebook-official"></i></a>';
                }
                if (isset($post->geodir_twitter) && $post->geodir_twitter) {
                    echo '<a href="' . $post->geodir_twitter . '"><i class="fa fa-twitter-square"></i></a>';
                }
                if (isset($post->geodir_contact) && $post->geodir_contact) {
                    echo '<a href="tel:' . $post->geodir_contact . '"><i class="fa fa-phone-square"></i>&nbsp;:&nbsp;' . $post->geodir_contact . '</a>';
                }
                echo '</div>';
                echo '<div class="sd-detail-cat-links"><ul>';
                foreach ($cats_arr as $cat) {
                    $term_arr = get_term($cat, $post_tax);
                    $term_icon = isset($cat_icons[$cat]) ? $cat_icons[$cat] : '';
                    $term_url = get_term_link(intval($cat), $post_tax);
                    echo '<li><a href="' . $term_url . '"><img src="' . $term_icon . '">';
                    echo '<span class="cat-link">' . $term_arr->name . '</span>';
                    echo '</a></li>';
                }
                echo '</ul></div> <!-- sd-detail-cat-links end --> </div> <!-- sd-detail-info end -->';
                echo '<div class="sd-detail-cta"><a class="dt-btn" href="' . get_the_permalink() . '#reviews">' . __('Write a Review', 'supreme-directory') . '</a>';
                ?>
                <div class="geodir_more_info geodir-company_info geodir_email" style="padding: 0;border: none">
                <?php
                if (!$preview) {
                    $html = '<input type="hidden" name="geodir_popup_post_id" value="' . $post->ID . '" />
                    <div class="geodir_display_popup_forms"></div>';
                    echo $html;
                }
                ?>
                    <span style="" class="geodir-i-email">
                    <i class="fa fa-envelope"></i>
                        <?php if (isset($post->geodir_email) && $post->geodir_email) {
                        ?>
                            <a href="javascript:void(0);" class="b_send_inquiry"><?php echo __('Send Enquiry', 'supreme-directory'); ?></a> | <?php } ?>
                        <a class="b_sendtofriend" href="javascript:void(0);"><?php echo __('Send To Friend', 'supreme-directory'); ?></a></span>

                </div>

                <?php
                geodir_favourite_html($post->post_author, $post->ID);
                ?>
                <ul class="sd-cta-favsandshare">
                    <?php if (!$preview) { ?>
                        <li><a target="_blank" title="<?php echo __('Share on Facebook', 'supreme-directory'); ?>"
                               href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>&t=<?php the_title(); ?>"><i
                                    class="fa fa-facebook"></i></a></li>
                        <li><a target="_blank" title="<?php echo __('Share on Twitter', 'supreme-directory'); ?>"
                               href="http://twitter.com/share?text=<?php echo urlencode(get_the_title()); ?>&url=<?php echo urlencode(get_the_permalink()); ?>"><i
                                    class="fa fa-twitter"></i></a></li>
                        <li><a target="_blank" title="<?php echo __('Share on Google Plus', 'supreme-directory'); ?>"
                               href="https://plus.google.com/share?url=<?php echo urlencode(get_the_permalink()); ?>"><i
                                    class="fa fa-google-plus"></i></a></li>
                    <?php } else { ?>
                        <li><a target="_blank" title="<?php echo __('Share on Facebook', 'supreme-directory'); ?>"
                               href=""><i class="fa fa-facebook"></i></a></li>
                        <li><a target="_blank" title="<?php echo __('Share on Twitter', 'supreme-directory'); ?>"
                               href=""><i class="fa fa-twitter"></i></a></li>
                        <li><a target="_blank" title="<?php echo __('Share on Google Plus', 'supreme-directory'); ?>"
                               href=""><i class="fa fa-google-plus"></i></a></li>
                    <?php } ?>
                </ul>
                <?php
                echo '</div><!-- sd-detail-cta end -->'; ?>

            </div>
            <!-- container end -->
        </div><!-- sd-detail-details end -->



    <?php } elseif ($page == 'home-page') {

        if (function_exists('geodir_get_location_seo')) {
            $seo = geodir_get_location_seo();
            if (isset($seo->seo_image_tagline) && $seo->seo_image_tagline) {
                $sub_title = $seo->seo_image_tagline;
            }
            if (isset($seo->seo_image) && $seo->seo_image) {
                $full_image_url = wp_get_attachment_image_src($seo->seo_image, 'full');
            }
        }

        if (isset($full_image_url)) {

        } elseif (has_post_thumbnail()) {
            $full_image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        } else {
            $full_image_url[0] = SD_DEFAULT_FEATURED_IMAGE;
        }

        if (!isset($sub_title) && get_post_meta(get_the_ID(), 'subtitle', true)) {
            $sub_title = get_post_meta(get_the_ID(), 'subtitle', true);
        }


        ?>
        <div class="featured-area">
            <div class="featured-img" style="background-image: url(<?php echo $full_image_url[0]; ?>);">

            </div>
            <div class="header-wrap">
            <?php do_action('sd_homepage_content');?>

            </div>
        </div>
    <?php
    }

}


function sd_homepage_featured_content(){

                if (is_singular() && $location = do_shortcode('[gd_current_location_name]')) { ?>
                    <h1 class="entry-title"><?php echo $location; ?></h1>
                <?php } else { ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php }

                if (isset($sub_title)) {
                    echo '<div class="entry-subtitle">' . $sub_title . '</div>';
                }

            echo do_shortcode('[gd_advanced_search]');
            echo do_shortcode('[gd_popular_post_category category_limit=5]');
            echo '<div class="home-more" id="sd-home-scroll"><a href="#sd-home-scroll" ><i class="fa fa-chevron-down"></i></a></div>';

}
add_action('sd_homepage_content','sd_homepage_featured_content');
