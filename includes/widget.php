<?php

function QuoteRegisterWidget() {
  register_widget('QuoteWidget');
}
add_action('widgets_init', 'QuoteRegisterWidget');

class QuoteWidget extends WP_Widget {

  public function __construct() {
    $widget_ops = array(
      'classname'   => 'widget_quote',
      'description' => __('quotes widget')
    );
    parent::__construct('quote-quotes', __('Quote'), $widget_ops);
    $this->alt_option_name = 'widget_quote';

    add_action('save_post', array(&$this, 'flushWidgetCache'));
    add_action('deleted_post', array(&$this, 'flushWidgetCache'));
    add_action('switch_theme', array(&$this, 'flushWidgetCache'));
  }

  public function widget($args, $instance) {
    // Retrieve cached data
    $cache = wp_cache_get('widget_quotes', 'widget');

    // Load Quote style.css
    wp_enqueue_style('quotes', plugins_url('style.css', __FILE__));

    if (!is_array($cache)) {
      $cache = array();
    }

    if (isset($cache[$args['widget_id']])) {
      echo $cache[$args['widget_id']];
      return;
    }

    // We don't have cached data : we create it!
    ob_start();
    extract($args);
    $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

    if (!$number = absint($instance['number'])) {
      $number = 1;
    }

    $query = array(
      'showposts'           => $number,
      'no_found_rows'       => true,
      'post_status'         => 'publish',
      'ignore_sticky_posts' => true,
      'post_type'           => 'quote'
    );

    if ($instance['random'] == true) {
      $query['orderby'] = 'rand';
    }

    if (!empty($instance['cat'])) {
      $query['tax_query'] = array(
        array(
          'taxonomy'         => 'quotes_category',
          'field'            => 'id',
          'terms'            => $instance['cat'],
          'include_children' => false
        )
      );
    }

    $r = new WP_Query($query);

    if ($r->have_posts()) {
      echo $before_widget;
      if ($title) {
        echo $before_title . $title . $after_title;
      }
      echo '<ul class="quote-quotes">';
      while ($r->have_posts()) {
        $r->the_post();
        ?>
        <li>
          <?php
          if (get_the_title()) {
            the_title();
          }

          $quote_author = get_post_meta(get_the_ID(), 'quote_author', true);
          $quote_where  = get_post_meta(get_the_ID(), 'quote_where', true);
          ?>
          <span>
            <?php
            if (!empty($quote_author)) {
              echo $quote_author;
            }

            if (!empty($quote_author) && !empty($quote_where)) {
              echo '<br />';
            }

            if (!empty($quote_where)) {
              echo $quote_where;
            }
            ?>
          </span>
        </li>
        <?php
      }
      echo '</ul>';
      echo $after_widget;

      // Reset the global $the_post as this query will have stomped on it
      wp_reset_postdata();
    }

    // Echo the result get it for caching
    $cache[$args['widget_id']] = ob_get_flush();
    wp_cache_set('widget_quotes', $cache, 'widget');
  }

  public function update($new_instance, $old_instance) {
    $instance           = $old_instance;
    $instance['title']  = strip_tags($new_instance['title']);
    $instance['number'] = (int) $new_instance['number'];
    $instance['random'] = strip_tags($new_instance['random']);
    $instance['cat']    = $new_instance['cat'];
    // Keep the data fresh
    $this->flushWidgetCache();

    $alloptions = wp_cache_get('alloptions', 'options');
    if (isset($alloptions['widget_quote'])) {
      delete_option('widget_quote');
    }

    return $instance;
  }

  public function flushWidgetCache() {
    wp_cache_delete('widget_quotes', 'widget');
  }

  public function form($instance) {
    $title = isset($instance['title']) ? esc_attr($instance['title']) : 'Quote';
    $number = isset($instance['number']) ? absint($instance['number']) : 1;
    $random = esc_attr($instance['random']);
    $cat = isset($instance['cat']) ? $instance['cat'] : array();
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title: (optional)'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __('Number of quotes to show:'); ?></label>
      <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
    </p>
    <p>
      <input type="checkbox" class="checkbox" name="<?php echo $this->get_field_name('random')?>" value="1" <?php checked( $random, 1 ); ?> />
      <label for="<?php echo $this->get_field_id('random'); ?>"><?php _e('Display random quote'); ?></label><br />
      <small>If random is not checked, the most recent quote(s) will be displayed</small>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('cat'); ?>"><?php echo __('Select Category(s):'); ?></label>
      <?php $terms = get_quotes_terms(); ?>
      <select class="widefat" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>[]" multiple="multiple">
        <?php
        foreach ($terms as $term) {
          $selected = (in_array($term->term_id, $cat)) ? ' selected' : '';
          echo '<option value="' . $term->term_id . '"' . $selected . '>' . $term->name . '</option>';
        }
        ?>
      </select>
    </p>
    <?php
  }
}
