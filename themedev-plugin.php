<?php
/*
Plugin Name: Site Plugin for themedev
Description: Site specific code changes for themedev
Author: Chazona Baum
Author URI: http://chazonabaum.com
*/

// Custom taxonomy for technologies

function create_technologies_taxonomy() {

  //UI labels
  $labels = array(
    'name' => _x( 'Technologies', 'Taxonomy General Name', 'chznbaumportfolio' ),
    'singular_name' => _x( 'Technology', 'Taxonomy Singular Name', 'chznbaumportfolio' ),
    'search_items' => __( 'Search Technologies', 'chznbaumportfolio' ),
    'all_items' => __( 'All Technologies', 'chznbaumportfolio' ),
    'parent_item' => __( 'Parent Technology', 'chznbaumportfolio' ),
    'parent_item_colon' => __( 'Parent Technology:', 'chznbaumportfolio' ),
    'edit_item' => __( 'Edit Technology', 'chznbaumportfolio' ),
    'update_item' => __( 'Update Technology', 'chznbaumportfolio' ),
    'add_new_item' => __( 'Add New Technology', 'chznbaumportfolio' ),
    'new_item_name' => __( 'Edit Technology', 'chznbaumportfolio' ),
    'menu_name' => __( 'Technologies', 'chznbaumportfolio' ),
  );

  // register the taxonomy
  register_taxonomy( 'technologies', array('portfolio'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'technology' ),
  ) );
}

// Hook technologies taxonomy to theme setup
add_action( 'init', 'create_technologies_taxonomy', 0 );

// Custom post type for portfolio
function create_portfolio_post_type() {

  //Set UI labels
  $labels = array(
    'name' => _x( 'Portfolio', 'Post Type General Name', 'chznbaumportfolio' ),
    'singular_name' => _x( 'Portfolio Item', 'Post Type Singular Name', 'chznbaumportfolio' ),
    'menu_name' => __( 'Portfolio', 'chznbaumportfolio' ),
    'parent_item' => __( 'Parent Portfolio Item', 'chznbaumportfolio' ),
    'parent_item_colon' => __( 'Parent Portfolio Item:', 'chznbaumportfolio' ),
    'all_items' => __( 'All Portfolio Items', 'chznbaumportfolio' ),
    'view_item' => __( 'View Portfolio Item', 'chznbaumportfolio' ),
    'add_new_item' => __( 'Add New Portfolio Item', 'chznbaumportfolio' ),
    'edit_item' => __( 'Edit Portfolio Item', 'chznbaumportfolio' ),
    'update_item' => __( 'Update Portfolio Item', 'chznbaumportfolio' ),
    'search_items' => __( 'Search Portfolio Items', 'chznbaumportfolio' ),
    'not_found' => __( 'Not Found', 'chznbaumportfolio' ),
    'not_found_in_trash' => __( 'Not Found In Trash', 'chznbaumportfolio' ),
  );

  //Set other options
  $args = array(
    'label' => __( 'portfolio', 'chznbaumportfolio' ),
    'description' => __( 'Showcase of completed portfolio items', 'chznbaumportfolio' ),
    'labels' => $labels,
    // Features this custom post types support in post editor
    'supports' => array( 'title', 'thumbnail', ),
    // Associate custom post type with a taxonomy or custom taxonomy
    'taxonomies' => array( 'technologies' ),
    /* A hierarchical CPT is like Pages and can have
    * Parent and child items. A non-hierarchical CPT
    * is like Posts.
    */
    'hierarchical' => false,
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => true,
    'show_in_admin_bar' => true,
    'menu_position' => 5,
    'can_export' => true,
    'has_archive' => false,
    'exclude_from_search' => false,
    'publicly_queryable' => true,
    'capability_type' => 'page',
  );

  register_post_type( 'portfolio', $args );

}

// Hook custom post types function to theme setup
add_action( 'init', 'create_portfolio_post_type', 0 );

function portfolio_meta_box_markup($object) {

  //nonce protection
  wp_nonce_field(basename(__FILE__), 'meta-box-nonce');

  ?>

  <label for="portfolio-meta-box-description"><strong>Description</strong></label>
  <p>Add a simple description to your project so that visitors can better understand it.</p>
  <textarea name="portfolio-meta-box-description" id="portfolio-meta-box-description" required><?php echo get_post_meta($object->ID, "portfolio-meta-box-description", true); ?></textarea>

  <label for="portfolio-meta-box-images"><strong>Images</strong></label>
  <p>Add images to your project, one below the other.</p>
  <?php wp_editor( get_post_meta($object->ID, 'portfolio-meta-box-images', true), 'portfolio-meta-box-images' ) ?>

  <?php

}

function add_portfolio_meta_box() {

  add_meta_box('portfolio-meta-box', 'Portfolio Item Content', 'portfolio_meta_box_markup', 'portfolio', 'normal', 'high', null);

}

add_action('add_meta_boxes', 'add_portfolio_meta_box');

function custom_meta_box_admin_styles() {

  global $typenow;
  if( $typenow == 'portfolio' ) {
    wp_enqueue_style( 'custom_meta_box_styles', plugin_dir_url( __FILE__ ) . 'metabox.css' );
  }

}

add_action( 'admin_print_styles', 'custom_meta_box_admin_styles' );

function save_custom_meta_box($post_id, $post, $update) {
  if (!isset($_POST['meta-box-nonce']) || !wp_verify_nonce($_POST['meta-box-nonce'], basename(__FILE__)))
    return $post_id;

  if(!current_user_can('edit_post', $post_id))
    return $post_id;

  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return $post_id;

  $slug = 'portfolio';
  if($slug != $post->post_type)
    return $post_id;

  $portfolio_meta_box_description_value = '';
  $portfolio_meta_box_images_value = '';

  if(isset($_POST['portfolio-meta-box-description'])) {
    $portfolio_meta_box_description_value = $_POST['portfolio-meta-box-description'];
  }
  update_post_meta($post_id, 'portfolio-meta-box-description', $portfolio_meta_box_description_value);

  if(isset($_POST['portfolio-meta-box-images'])) {
    $portfolio_meta_box_images_value = $_POST['portfolio-meta-box-images'];
  }
  update_post_meta($post_id, 'portfolio-meta-box-images', $portfolio_meta_box_images_value);
  
}

add_action('save_post', 'save_custom_meta_box', 10, 3);

// Remove unnecessary field meta boxes
function remove_custom_field_meta_boxes() {
  remove_meta_box('formatdiv', 'portfolio', 'normal');
  remove_meta_box('trackbacksdiv', 'portfolio', 'normal');
}

add_action('do_meta_boxes', 'remove_custom_field_meta_boxes');

?>