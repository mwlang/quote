<?php
function quotes_form() {
  global $post;
  $author = get_post_meta($post->ID, 'quote_author', true);
  $author_email = get_post_meta($post->ID, 'quote_author_email', true);
  $hide_author = get_post_meta($post->ID, 'quote_hide_author', true);
  $where  = get_post_meta($post->ID, 'quote_where', true);
  ?>
  <div class="input-checkbox-div">
    <label class="quote-field-checkbox-label" for="quote_hide_author">Hide Author Name</label>
    <input class="quote-field-checkbox" type="checkbox" id="quote_hide_author" name="quote_hide_author" <?php if ($hide_author) { ?>checked="checked"<?php } ?>">
  </div>
  <div class="input-div">
    <label class="quote-field-label" for="quote_author">Quote Author (optional)</label>
    <input type="text" value="<?php echo $author; ?>" id="quote_author" class="text" name="quote_author">
  </div>
  <div class="input-div last">
    <label class="quote-field-label" for="quote_where">Where is the quote or author from? (optional)</label>
    <input type="text" value="<?php echo $where; ?>" id="quote_where" class="text" name="quote_where">
  </div>
  <div class="input-div">
    <label class="quote-field-label" for="quote_author_email">Author E-mail (optional)</label>
    <input type="text" value="<?php echo $author_email; ?>" id="quote_author_email" class="text" name="quote_author_email">
  </div>
  <?php
}
