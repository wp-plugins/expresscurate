<?php
$settings = array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'tinymce' => true, 'quicktags' => false);
?>
<div>
  <div class="content_editor" id ="expresscurate_post_form">
    <div class="main">
      <div class="header">
        <div class="addressbar">
          <input type="text" placeholder="<?php echo __('Insert source URL to start', ExpressCurate_Settings::PLUGIN_FOLDER) ?>" id="expresscurate_source" name="expresscurate_source" value="<?php echo @get_post_meta($post->ID, 'expresscurate_source', true); ?>">
          <input type="hidden" id="expresscurate_admin_url" value="<?php echo admin_url(); ?>" />
          <button class="load"  id="expresscurate_submit">Load</button>
        </div>
        <div class="title">
          <input type="text" value="" id="curated_title">
        </div>
      </div>
      <div class="content">
        <div class="hidden">
          <ul id="curated_images">
          </ul>
        </div>
        <div class="left imgContainer">
            <div class="imgIcons">
                <div class="sizeX"></div>
                <div class="sizeM"></div>
                <div class="sizeS"></div>

                <div class="prevImg prev"></div>
                <div class="nextImg next"></div>

                <div class="alignL"></div>
                <div class="alignNone"></div>
                <div class="alignR"></div>
            </div>
            <div class="img noimage">
                <!--
              <div class="nav">
                <a href="#" class="prev left" onclick="return false;">
                  <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/arrow_small.png">
                </a>
                <a href="#" class="next right" onclick="return false;">
                  <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/arrow_small.png">
                </a>
                <div class="clear"></div>
              </div>
              -->
            </div>
        </div>
        <div class="editor right">
          <?php wp_editor('', 'expresscurate_content_editor', $settings); ?>

          <div class="clear"></div>
        </div>
      </div>
      <div class="clear"></div>
      <div class="controls hidden">
        <ul class="tags" id="expresscurate_special">

        </ul>
        <div class="clear"></div>
        <div id="expresscurate_slider" class="jcarousel-container">
          <ul class="preview left jcarousel-skin-tango" id="curated_paragraphs">
          </ul>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
    <div class="footer">
      <input type="hidden" value="<?php echo get_option('expresscurate_curated_text', 'Curated from'); ?>" id="expresscurate_from" name="expresscurate_from"/>
      <input type="hidden" value="<?php echo get_option('expresscurate_autosummary', 5); ?>" id="expresscurate_autosummary" name="expresscurate_autosummary"/>
      <ul class="labels" id="curated_tags">
      </ul>
      <div class="clear"></div>
      <button class="curate right" id="expresscurate_insert" onclick="return false;"><?php echo __('Curate into post', ExpressCurate_Settings::PLUGIN_FOLDER) ?></button>
      <div class="clear"></div>
    </div>
  </div>
</div>
<div id="expresscurate_loading">
  <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/loading.gif" id="img-load" />
</div>


