<?php
/*
* Plugin Name: Related Categories Post
* Description: This Plugin is use for Display Related Categories post.
* Version:     1.0.0
* Author:      Shail Mehta
* Author URI:  https://profiles.wordpress.org/mehtashail/
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wordpress.org
*/
if (!class_exists('sm_related_categories_post')) {
    class Sm_related_categories_post
    {
        function sm_related_categories_post_install()
        {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }

        function activate()
        {
            flush_rewrite_rules();
        }

        function deactivate()
        {
            flush_rewrite_rules();
        }
    }

    // activation and deactivation
    $sm_related_categories_post = new Sm_related_categories_post();
    register_activation_hook(__FILE__, array($sm_related_categories_post, 'activate'));
    register_deactivation_hook(__FILE__, array($sm_related_categories_post, 'deactivate'));
//Related Categories Post
    function sm_get_related_posts( $post_id, $related_count, $args = array() )
    {
        $terms = get_the_terms($post_id, 'category');

        if (empty($terms)) $terms = array();

        $term_list = wp_list_pluck($terms, 'slug');

        $related_args = array(
            'post_type' => 'post',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'post__not_in' => array($post_id),
            'orderby' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $term_list
                )
            )
        );
        return new WP_Query($related_args);
    }
    add_action('init', 'sm_get_related_posts_enqueue');
    function sm_get_related_posts_enqueue()
    {
        // enqueue style
        wp_enqueue_style('Related Post Listing', plugin_dir_url(__FILE__) . 'css/related-category-style.css', array(), false, 'all');
    }

    function sm_get_related_post_list(){
        global  $post;
        $related = sm_get_related_posts(get_the_ID(),-1);
        //print_r($related);
        if ($related->have_posts()):
            ?>
           <?php  if ( is_single() ){ ?>
            <div class="post-navigation related-post-cat">
                <h3 class="widget-title">Related Post</h3>
                <ul class="related-posts-cats">
                    <?php while ($related->have_posts()): $related->the_post(); ?>
                        <li>
                            <div class="catpost-title-related"><a class="related-post-cat-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail(array(60,60)); ?></a><a class="related-post-cat-post-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
                        </li>

                    <?php endwhile; ?>
                </ul>
            </div>

        <?php }
        endif;
    }

    add_shortcode('related_post_list','sm_get_related_post_list');
    add_action( 'widgets_init', function(){
        register_widget( 'Related_Category_Widget' );
    });
    class Related_Category_Widget extends WP_Widget {
        // class constructor
        public function __construct() {

            $widget_ops = array(
                'classname' => 'related_category_widget related-cat-listing cat-listing',
                'description' => 'Related Categories Post Display',
            );
            parent::__construct( 'related_category_widget', 'Related Categories Posts', $widget_ops );
        }

        // output the widget content on the front-end
        public function widget( $args, $instance ) {
            echo $args['before_widget'];
            if ( ! empty( $instance['title'] ) ) {

                $post_cat=$instance['title'];
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];

            }
            echo do_shortcode( '[related_post_list]' );
            echo $args['after_widget'];
        }
    }

}