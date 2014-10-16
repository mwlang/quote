<?php
/*
Plugin Name: quote
Plugin URI: http://codeconnoisseur.org
Description: An easy to use plugin for managing quotes and testimonials!
Version: 0.0.1
Author: Michael Lang
Author URI: http://codeconnoisseur.org
License: GPL2
*/

require 'includes/post-type.php';
require 'includes/widget.php';
require 'includes/widget-single.php';
require 'includes/shortcodes.php';
require 'includes/quotes_form.php';

add_action('admin_head', 'quotes_admin_css');

// Custom Columns
add_action("manage_posts_custom_column",  "quotes_columns");
add_filter("manage_edit-quote_columns", "quotes_edit_columns");

function quotes_edit_columns($columns){
  $columns = array(
    'cb'                   => "<input type=\"checkbox\" />",
    'title'                => 'Title',
    'quote-author'         => 'Author',
    'shortcode'            => 'Shortcode',
    'quote'                => 'Quote',
  );

  return $columns;
}

function quotes_columns($column){
  global $post;

  switch ($column) {
    case 'quote': 
      $content = (strlen($post->post_content) > 255) ? substr($post->post_content, 0, 255) . '...' : $post->post_content;
      echo $content;
      break;
    case 'quote-author':
      echo get_post_meta($post->ID, 'quote_author', true);
      break;
    case 'shortcode':
      echo '[quotes id="' . $post->ID . '"]';
      break;
    case 'category':
      the_terms( $post->ID, 'quotes_category');
      break;
  }
}

// Change the default "Enter title here" text
function quotes_post_title($title) {
  $screen = get_current_screen();
  if ('quote' == $screen->post_type) {
    $title = 'Enter title';
  }
  return $title;
}
add_filter('enter_title_here', 'quotes_post_title');

// Add filter for Quote
add_filter( 'post_updated_messages', 'quote_updated_messages' );
function quote_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['quote-quotes'] = array(
    0  => '', // Unused. Messages start at index 1.
    1  => sprintf( __('Quote updated. <a href="%s">View quote</a>'), esc_url( get_permalink($post_ID) ) ),
    2  => __('Custom field updated.'),
    3  => __('Custom field deleted.'),
    4  => __('Quote updated.'),
    5  => isset($_GET['revision']) ? sprintf( __('Quote restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6  => sprintf( __('Quote published. <a href="%s">View quote</a>'), esc_url( get_permalink($post_ID) ) ),
    7  => __('Quote saved.'),
    8  => sprintf( __('Quote submitted. <a target="_blank" href="%s">Preview quote</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9  => sprintf( __('Quote scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview quote</a>'),
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Quote draft updated. <a target="_blank" href="%s">Preview quote</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

// Display contextual help for Quote
add_action( 'contextual_help', 'quote_add_help_text', 10, 3 );

function quote_add_help_text( $contextual_help, $screen_id, $screen ) {
  if ( 'quote-quotes' == $screen->id ) {
    $contextual_help =
      '<p><strong>' . __('Things to remember when adding or editing a <em>Quote</em>:') . '</strong></p>' .
      '<ul>' .
        '<li>' . __('Just type in the <em>Quote</em> you want! It\'s that easy!') . '</li>' .
        '<li>' . __('If you want to include the source of the quote, just add it in the appropriate input field!') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('If you want to schedule the <em>Quote</em> to be published in the future:') . '</strong></p>' .
      '<ul>' .
        '<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
        '<li>' . __('Change the date to when you actually publish the quote, then click on OK.') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('For more information:') . '</strong></p>' .
      '<p>' . __('<a href="http://quotespace.com/" target="_blank">Visit quoteSpace.com</a>') . '</p>';
  }
  return $contextual_help;
}

function quotes_meta_boxes() {
  global $post;
  $pagename = 'quote';
  add_meta_box( 'quotes_form', 'Quote Author', 'quotes_form', $pagename, 'normal', 'high' );
}

function quotes_save_postdata($post_id) {
  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $post_id;
  } // end if

  // Check user permissions
  if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
    if (!current_user_can('edit_page', $post_id)) return $post_id;
  } else {
    if (!current_user_can('edit_post', $post_id)) return $post_id;
  }

  // OK, we're authenticated: we need to find and save the data
  $current_author_email = get_post_meta($post_id, 'quote_author_email', false);
  $new_author_email = (isset($_POST['quote_author_email'])) ? $_POST['quote_author_email'] : '';
  $current_author = get_post_meta($post_id, 'quote_author', false);
  $new_author = (isset($_POST['quote_author'])) ? $_POST['quote_author'] : '';
  $current_where  = get_post_meta($post_id, 'quote_where', false);
  $new_where = (isset($_POST['quote_where'])) ? $_POST['quote_where'] : '' ;
  
  $hide_author = isset($_POST['quote_hide_author']);

  quotes_clean($new_author);
  quotes_clean($new_author_email);
  quotes_clean($new_where);

  if (!empty($current_author)) {
    if (is_null($new_author)) {
      delete_post_meta($post_id,'quote_author');
    } else {
      update_post_meta($post_id,'quote_author',$new_author);
    }
  } elseif (!is_null($new_author)) {
      add_post_meta($post_id,'quote_author',$new_author,true);
  }

  if (!empty($current_author_email)) {
    if (is_null($new_author_email)) {
      delete_post_meta($post_id,'quote_author_email');
    } else {
      update_post_meta($post_id,'quote_author_email',$new_author_email);
    }
  } elseif (!is_null($new_author_email)) {
      add_post_meta($post_id,'quote_author_email',$new_author_email,true);
  }

  if (!empty($current_where)) {
    if (is_null($new_where)) {
      delete_post_meta($post_id,'quote_where');
    } else {
      update_post_meta($post_id,'quote_where',$new_where);
    }
  } elseif (!is_null($new_where)) {
      add_post_meta($post_id,'quote_where',$new_where,true);
  }

  update_post_meta($post_id, 'quote_hide_author', $hide_author);

  return $post_id;
}

function quotes_clean(&$arr) {
  if (is_array($arr)) {
    foreach ($arr as $i => $v) {
      if (is_array($arr[$i])) {
        my_meta_clean($arr[$i]);
        if (!count($arr[$i])) {
          unset($arr[$i]);
        }
      } else {
        if (trim($arr[$i]) == '') {
          unset($arr[$i]);
        }
      }
    }
    if (!count($arr)) {
      $arr = NULL;
    }
  }
}

function quotes_admin_css() {
  echo '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url(__FILE__) . 'includes/admin.css" />';
}

// Function to retrieve all Quote categories
function get_quotes_terms($parent = null, $hide_empty = false) {
  $args = array(
    'hide_empty' => $hide_empty,
    'orderby' => 'name',
  );
  if ($parent !== null) {
    $args['parent'] = $parent;
  }

  $terms = get_terms(
    'quotes_category',
    $args
  );

  // foreach($terms as $key => $term) {
  //   $terms[$key]->children = get_quotes_terms($term->term_id);
  // }
  return $terms;
}
