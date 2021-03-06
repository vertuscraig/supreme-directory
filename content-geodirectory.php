<?php global $dt_blog_sidebar_position,$sd_sidebar_class,$post;?>
<article <?php post_class(); ?>>
    <div class="container" id="home-scroll">

        <?php
        if($dt_blog_sidebar_position=='left'){?>
        <div class="sd-sidebar sd-sidebar-left">
            <div class="sidebar blog-sidebar page-sidebar">
                <?php dynamic_sidebar('sidebar-gd');?>
            </div>
        </div>
        <?php }?>

        <div class="entry-content entry-summary <?php echo $sd_sidebar_class;?>">
            <?php

            // add the title if its not added in the featured area
            $post_id = $post->ID;
            if(function_exists('geodir_is_page') && geodir_is_page('single') && isset($post->post_type)){
                $page_id = geodir_cpt_template_page('page_details',$post->post_type);
                if($page_id){
                    $post_id = $page_id;
                }
            }
            $featured_type  = get_post_meta($post_id, '_sd_featured_area', true);
            if($featured_type == 'remove'){
                ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php
            }

            global $more;
            $more = 0;
            if (is_singular() || ( function_exists('is_bbpress') && is_bbpress() )) {
                the_content();
            } else {
                directory_theme_post_thumbnail();
                the_excerpt();
            }
            ?>
            <?php
            wp_link_pages(array(
                'before' => '<div class="page-links"><span class="page-links-title">' . __('Pages:', 'supreme-directory') . '</span>',
                'after' => '</div>',
                'link_before' => '<span>',
                'link_after' => '</span>',
            ));
            ?>
            <footer class="entry-footer">
                <?php directory_theme_entry_meta(); ?>
                <?php edit_post_link(__('Edit', 'supreme-directory'), '<span class="edit-link">', '</span>'); ?>



                <?php
                global $post;

                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) : ?>
                <div class="comments-container">
                    <?php comments_template(); ?>
                </div>
                    <?php endif;
                ?>




            </footer>
        </div>

        <?php
        if($dt_blog_sidebar_position=='right'){?>
            <div class="sd-sidebar sd-sidebar-right">
                <div class="sidebar blog-sidebar page-sidebar">
                    <?php dynamic_sidebar('sidebar-gd'); ?>
                </div>
            </div>
        <?php }?>

    </div>
</article>