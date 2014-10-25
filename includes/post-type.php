<?php
/**
 * Quote custom post type
 */
function quotes_init() {
  $labels = array(
    'name'               => __('Quotes', 'post type general name'),
    'singular_name'      => __('Quote', 'post type singular name'),
    'add_new'            => __('Add new', 'member'),
    'add_new_item'       => __('Add new Quote'),
    'edit_item'          => __('Edit Quote'),
    'new_item'           => __('New Quote'),
    'view_item'          => __('View Quote'),
    'search_items'       => __('Search Quotes'),
    'not_found'          =>  __('No quotes found!'),
    'not_found_in_trash' => __('No quotes in the trash!'),
    'parent_item_colon'  => ''
  );

  $args = array(
    'labels'               => $labels,
    'public'               => true,
    'publicly_queryable'   => true,
    'show_ui'              => true,
    'query_var'            => true,
    'rewrite'              => array('slug' => 'quotes'),
    'capability_type'      => 'post',
    'hierarchical'         => false,
    'has_archive'          => true,
    'menu_position'        => 100,
    'menu_icon'            => plugins_url('images/quotes.png', dirname(__FILE__)),
    'supports'             => array('title', 'editor', 'excerpt'),
    'register_meta_box_cb' => 'quotes_meta_boxes'
  );

  register_post_type('quote', $args);
  add_action( 'save_post', 'quotes_save_postdata' );
}
add_action('init', 'quotes_init');

/**
 * Quote Category taxonomy
 */
function quotes_taxonomy_init() {
  $labels = array(
    'name'              => 'Categories',
    'singular_name'     => 'Category',
    'search_items'      => 'Search Categories',
    'all_items'         => 'All Categories',
    'parent_item'       => 'Parent Category',
    'parent_item_colon' => 'Parent Category:',
    'edit_item'         => 'Edit Category',
    'update_item'       => 'Update Category',
    'add_new_item'      => 'Add New Category',
    'new_item_name'     => 'New Category Name',
    'menu_name'         => 'Categories'
  );

  $args = array(
    'hierarchical' => true,
    'labels'       => $labels,
    'show_ui'      => true,
    'query_var'    => true,
    'rewrite'      => array('slug' => 'quotes-categories'),
  );
  register_taxonomy('quotes_category', 'quote', $args);
}
add_action('init', 'quotes_taxonomy_init');

