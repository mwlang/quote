<?php
/* ------------------------------------ */
/* Shortcode Generator                  */
/* ------------------------------------ */

// Action target that adds the "Insert Product(s)" button to the post/page edit screen.
function jsAddQuoteButton($context) {
    $image_btn = plugins_url('images/quote-space-icon.png', dirname(__FILE__));
    $out = '<a href="#TB_inline?width=450&height=700&inlineId=insert_quote" class="button thickbox" title="Insert Quote"><img src="'.$image_btn.'" alt="Insert Quote" /> Add Quote</a>';
    return $context . $out;
}
add_action('media_buttons_context', 'jsAddQuoteButton');

//Action target that displays the popup to insert a form to a post/page
function jsAddQuotePopup() {
  $quotes = get_posts(array('post_type' => 'quote'));
  foreach ($quotes as $quote) {
    $quote_data[$quote->ID] = (strlen($quote->post_content) > 40) ? substr($quote->post_content, 0, 37) . '...' : $quote->post_content;
  }
  ?>
  <div id="insert_quote" class="folded" style="display:none;">
    <div class="wrap">
      <div>
        <div style="padding:15px 15px 0 15px;">
          <h3 class="media-title">Quote Shortcode Generator</h3>
          <span>Please do one of the following to display your quote(s):</span>
        </div>
        <div style="padding:15px 15px 0 15px;">
          <label style="display:block;color:#21759B;font-size:12px;font-weight:bold;padding:0 0 8px;text-shadow:0 1px 0 #FFFFFF;">Either select a specific quote</label>
          <select id="js_quotes_shortcode_select">
            <option value="">Select a quote...</option>
            <?php foreach ($quote_data as $key => $val) : ?>
              <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="padding:15px 15px 0 15px;">
           OR
        </div>
        <div style="padding:15px 15px 0 15px;">
          <label style="display:block;color:#21759B;font-size:12px;font-weight:bold;padding:0 0 8px;text-shadow:0 1px 0 #FFFFFF;">Choose a category</label>
          <span style="color:#999;display:block;font-size:10px; margin-bottom:8px;">Not checking random and entering a number in the number field will result in the most recent quote(s) being shown</span>
          <table class="describe">
            <tbody>
              <tr>
                <th class="label" style="width:200px;">Select category(s)?</th>
                <td class="field">
                  <?php $terms = get_quotes_terms(); ?>
                  <select id="js_quote_shortcode_cat" multiple="multiple">
                    <?php
                    foreach ($terms as $term) {
                      echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th class="label" style="width:200px;">Display random quote(s)?</th>
                <td class="field">
                  <input type="checkbox" id="js_quote_shortcode_random" name="random" value="1" />

                </td>
              </tr>
              <tr>
                <th class="label" style="width:200px;">Display excerpt quote(s)?</th>
                <td class="field">
                  <input type="checkbox" id="js_quote_shortcode_except" name="excerpt" value="1" />

                </td>
              </tr>
              <tr>
                <th class="label" style="width:200px;">Number of quotes</th>
                <td class="field">
                  <input type="text" id="js_quote_shortcode_number" name="number" /><br/>
                  <span style="color:#999;display:block;font-size:10px;">Enter "-1" or "all" to show all quotes</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div style="padding:15px;">
          <input type="button" class="button-primary" value="Insert into Post" onclick="InsertquoteQuote();"/>&nbsp;&nbsp;&nbsp;
          <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;">Cancel</a>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">

    function InsertquoteQuote() {
      var quote  = jQuery("#js_quotes_shortcode_select").val();
      var random = jQuery("#js_quote_shortcode_random").attr('checked') ? ' random="true"' : '';
      var except = jQuery("#js_quote_shortcode_excerpt").attr('checked') ? ' excerpt="true"' : '';
      var number = jQuery("#js_quote_shortcode_number").val();
      var cat    = jQuery("#js_quote_shortcode_cat").val();
      var win    = window.dialogArguments || opener || parent || top;

      cat = cat ? ' cat="' + cat.join(',') + '"' : '';

      if (quote){
        select = jQuery("#js_quotes_shortcode_select");
        select.val(jQuery('options:first', select).val());
        win.send_to_editor('[quotes id="' + quote + excerpt + '"]');
      } else if (number && (jQuery.isNumeric(number) || number == 'all')) {
        jQuery("#js_quote_shortcode_number").attr('value', '');
        jQuery("#js_quote_shortcode_random").removeAttr('checked');
        win.send_to_editor('[quotes num="' + number + '"' + random + cat + excerpt + ']');
      } else if (random) {
        jQuery("#js_quote_shortcode_random").removeAttr('checked');
        win.send_to_editor('[quotes' + random + cat + excerpt + ']');
      } else {
        alert("Please select or enter a valid option(s) to display a quote");
        return;
      }
    }

  </script>

    <?php
}
add_action('admin_footer', 'jsAddQuotePopup');

// Shortcode [quotes id="10"]
function QuoteFunc($atts) {
  extract(shortcode_atts(array('id' => null, 'num' => null, 'random' => null, 'cat' => null, 'excerpt' => null), $atts));
  if ($id == null && $num == null & $random == null) {
    return false;
  } else if ($id) {
    $args = array(
      'p'         => $id,
      'post_type' => 'quote'
    );
  } else if ($num) {
    $num = ($num == 'all') ? -1 : $num;
    $args = array(
      'posts_per_page' => $num,
      'post_type'      => 'quote'
    );
    if ($random) {
      $args['orderby'] = 'rand';
    }
  } else if ($random) {
    $args = array(
      'posts_per_page' => 1,
      'orderby'        => 'rand',
      'post_type'      => 'quote'
    );
  }
  if (!empty($cat)) {
    $args['tax_query'] = array(
      array(
        'taxonomy'         => 'quotes_category',
        'field'            => 'id',
        'terms'            => array_map('intval', explode(',', $cat)),
        'include_children' => false
      )
    );
  }

  // Load Quote style.css
  wp_enqueue_style('quotes', plugins_url('style.css', __FILE__));

  ob_start();

  $query = new WP_Query($args);

  echo "<table>";
  
  while ($query->have_posts()) {
    $query->the_post();
    $post = get_post();

    echo "<tr><td class=quote>";

    if ($excerpt) {
      $quote = $post->post_excerpt ? : $post->post_content ? : "No Excerpt!";
    } else {
      $quote = $post->post_content ? : "No Quote!";
    }

    echo "<blockquote>".$quote."</blockquote>";
    
    echo "</td><td class=person>";
    
    $quote_author = get_post_meta(get_the_ID(), 'quote_author', true);
    $quote_author_email = get_post_meta(get_the_ID(), 'quote_author_email', true);
    $quote_where  = get_post_meta(get_the_ID(), 'quote_where', true);
    $hide_author  = get_post_meta(get_the_ID(), 'quote_hide_author', true);

    if (!empty($quote_author) || !empty($quote_where)) {
      if (!$hide_author) {
        if (!empty($quote_author_email)) { echo get_avatar($quote_author_email) . '<br/>'; };
        if (!empty($quote_author)) { echo '<span>'.$quote_author.'</span><br/>'; };
      }

      if (!empty($quote_where)) {
        echo '<span>'.$quote_where.'</span>';
      }
    }
    
    echo '</td></tr>';
    
  }
  echo "</table>";
  
  wp_reset_postdata();
  $content = ob_get_clean();
  return $content;
}
add_shortcode('quotes', 'QuoteFunc');
