<?php
  global $post;
?>
<a name="expresscurate" id="expresscurate"></a>
<table width="100%" border="0">
  <tr>
    <td><label><input name="expresscurate_keywords" id="expresscurate_keywords" type="text" value="<?php echo get_post_meta($post->ID,'_expresscurate_keywords', true)?>" /><span class="expresscurate_text"> <?php _e('Keywords (comma separated)', 'expresscurate'); ?></span></label></td>
  </tr>
  <tr>
    <td><label><textarea name="expresscurate_description" id="expresscurate_description"><?php echo get_post_meta($post->ID,'_expresscurate_description', true)?></textarea><span class="expresscurate_text"> <?php _e('Description', 'expresscurate'); ?></span></label></td>
  </tr>
</table>
