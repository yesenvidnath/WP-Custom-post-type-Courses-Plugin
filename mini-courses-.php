<?php 
/*
Plugin Name: Affiliate
Plugin URI: www.webstronomy.com/plugins
Description: This is for Affiliate Links for the Website
Version: 1.0.0
Author: Webstronomy (Pvt) Ltd
Author URI: www.webstronomy.com
Text Domain: Mc
*/

// If this file is called directly, abort. //
if ( ! defined( 'WPINC' ) ) {die;} // end if

// Let's Initialize Everything
if ( file_exists( plugin_dir_path( __FILE__ ) . 'core-init.php' ) ) {
require_once( plugin_dir_path( __FILE__ ) . 'core-init.php' );
}

// Add menu item in the admin panel
function add_mini_courses_menu() {
    add_menu_page(
        'Mini Courses',
        'Mini Courses',
        'manage_options',
        'mini-courses-settings',
        'mini_courses_settings_page',
        'dashicons-welcome-learn-more', // Icon
        20 // Menu position
    );
}

// Register Custom Post Type
function register_mini_courses_post_type() {
    $labels = array(
        'name'               => 'Courses',
        'singular_name'      => 'Course',
        'menu_name'          => 'Mini Courses',
        'all_items'          => 'All Courses',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Course',
        'edit_item'          => 'Edit Course',
        'new_item'           => 'New Course',
        'view_item'          => 'View Course',
        'search_items'       => 'Search Courses',
        'not_found'          => 'No courses found',
        'not_found_in_trash' => 'No courses found in Trash',
    );

     $args = array(
        'public'       => true,
        'label'        => 'Courses',
        'labels'       => $labels,
        'supports'     => array('title', 'editor', 'thumbnail'),
        'menu_icon'    => 'dashicons-welcome-learn-more',
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'courses'),
    );

     register_post_type('mini_courses', $args);
}
add_action('init', 'register_mini_courses_post_type');

// Add Meta Boxes for Course Details
function add_course_meta_boxes() {
    add_meta_box('course_details', 'Course Details', 'render_course_details_meta_box', 'mini_courses', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_course_meta_boxes');

function render_course_details_meta_box($post) {
    // Retrieve existing values for the fields
    $course_name = get_post_meta($post->ID, '_course_name', true);
    $course_description = get_post_meta($post->ID, '_course_description', true);
    $course_btn_link = get_post_meta($post->ID, '_course_btn_link', true);
    $course_image_url = get_post_meta($post->ID, '_course_image_url', true);

    // Output fields with Bootstrap styling
    ?>
    <div class="form-group">
        <label for="course_name">Course Name:</label>
        <input type="text" class="form-control" name="course_name" value="<?php echo esc_attr($course_name); ?>" />
    </div>

    <div class="form-group">
        <label for="course_description">Course Description:</label>
        <textarea class="form-control" name="course_description"><?php echo esc_textarea($course_description); ?></textarea>
    </div>

    <div class="form-group">
        <label for="course_btn_link">Course Button Link:</label>
        <input type="text" class="form-control" name="course_btn_link" value="<?php echo esc_url($course_btn_link); ?>" />
    </div>

    <div class="form-group">
        <label for="course_image_url">Course Image URL:</label>
        <input type="text" name="course_image_url" value="<?php echo esc_url($course_image_url); ?>" />
    </div>
    
    <?php
}

function save_course_details($post_id) {
    // Save course details
    if (isset($_POST['course_name'])) {
        update_post_meta($post_id, '_course_name', sanitize_text_field($_POST['course_name']));
    }

    if (isset($_POST['course_description'])) {
        update_post_meta($post_id, '_course_description', sanitize_textarea_field($_POST['course_description']));
    }

    if (isset($_POST['course_btn_link'])) {
        update_post_meta($post_id, '_course_btn_link', esc_url($_POST['course_btn_link']));
    }

    if (isset($_POST['course_image_url'])) {
        update_post_meta($post_id, '_course_image_url', esc_url($_POST['course_image_url']));
    }
}

add_action('save_post', 'save_course_details');


// Shortcode for Displaying Courses
function display_courses_shortcode($atts) {
    ob_start();

    // Query to retrieve courses
    $query = new WP_Query(array(
        'post_type' => 'mini_courses',
        'posts_per_page' => -1,
    ));

    if ($query->have_posts()) {
        ?>
        <div class="row">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col-md-4 mb-2">
                    <?php
                    $course_btn_link = get_post_meta(get_the_ID(), '_course_btn_link', true);

                    // Check if the course URL is available
                    if ($course_btn_link) {
                        // Open a link tag with the course URL
                        echo '<a href="' . esc_url($course_btn_link) . '" class="card-link" target="_blank">';
                    }
                    ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php
                                    $course_image_url = get_post_meta(get_the_ID(), '_course_image_url', true);

                                    // Check if the value is empty
                                    if (empty($course_image_url)) {
                                        // Set default image URL
                                        $default_image_url = 'https://tiburontactical.webstronomy.com/wp-content/uploads/2023/12/ZCNVKD3-1.png';
                                        $course_image_url = esc_url($default_image_url);
                                    }
                                ?> 

                                <img data-src="<?php echo $course_image_url; ?>" class="card-img-top lazyload" alt="<?php the_title(); ?>">

                            <?php endif; ?>

                            <h5 class="card-title"><?php the_title(); ?></h5>
                            <p class="card-text"><?php echo esc_html(get_post_meta(get_the_ID(), '_course_description', true)); ?></p>
                            <?php if ($course_btn_link = get_post_meta(get_the_ID(), '_course_btn_link', true)) : ?>
                                <a href="<?php echo esc_url($course_btn_link); ?>" target="_blank" class="btn btn-primary">Learn More</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php
                        // Close the link tag if the course URL is available
                        if ($course_btn_link) {
                            echo '</a>';
                        }
                    ?> 

                </div>
            <?php endwhile; ?>
        </div>
        <?php        
    } else {
        // Display "Coming Soon" when there are no courses
        echo '<p>Coming Soon</p>';
    }

    wp_reset_postdata();
    ?>
    <script>
        // Add lazy loading to images using the lazysizes library
        document.addEventListener('DOMContentLoaded', function () {
            if ('loading' in HTMLImageElement.prototype) {
                var lazyImages = document.querySelectorAll('.lazyload');
                lazyImages.forEach(function (img) {
                    img.src = img.dataset.src;
                    img.onload = function () {
                        img.removeAttribute('data-src');
                    };
                });
            } else {
                // Include the lazysizes library for older browsers
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
                document.body.appendChild(script);
            }
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('display_courses', 'display_courses_shortcode');



// Add a custom class to the body when inside your plugin settings page
function add_custom_body_class($classes) {
    global $post;

    // Check if we are in the admin area and editing a post
    if (is_admin() && isset($post->ID) && get_post_type($post->ID) === 'mini_courses') {
        $classes .= ' mini-courses-plugin';
    }

    return $classes;
}
add_filter('admin_body_class', 'add_custom_body_class');

// Add styles specific to your plugin
function add_custom_styles() {
    ?>
    <style>
        /* Styles only apply inside the Mini Courses plugin */
        body.mini-courses-plugin div#postdivrich {
            display: none !important;
        }

        body.mini-courses-plugin div#postimagediv {
            /* display: none !important; */
        }

        body.mini-courses-plugin div#astra_settings_meta_box {
            display: none !important;
        }

        body.mini-courses-plugin div#litespeed_meta_boxes {
            display: none !important;
        }

        .form-group {
            display: flex;
            width: 30%;
            padding: 1em 20px;
        }

        label {
            width: 50%;
        }

        input.form-control {
            width: 50%;
        }

        .frm-img-group-selection{
            display: none;
        }

    </style>
    <?php
}

add_action('admin_head', 'add_custom_styles');
