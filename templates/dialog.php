<?php
$settings = array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'tinymce' => true, 'quicktags' => false);
?>
<div>
  <div class="content_editor expresscurate_Styles" id ="expresscurate_post_form">
    <div class="main">
      <div class="header">
        <div class="addressbar">
          <input type="text" placeholder="<?php echo __('Insert source URL to start', ExpressCurate_Actions::PLUGIN_FOLDER) ?>" id="expresscurate_source" name="expresscurate_source" value="<?php echo @get_post_meta($post->ID, 'expresscurate_source', true); ?>">
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
                <div class="sizeX active">
                    <div class="tooltipWrap">
                        <span>Large size</span>
                    </div>
                </div>
                <div class="sizeM">
                    <div class="tooltipWrap">
                        <span>Middle size</span>
                    </div>
                </div>
                <div class="sizeS">
                    <div class="tooltipWrap">
                        <span>Small size</span>
                    </div>
                </div>

                <div class="prevImg prev">
                    <div class="tooltipWrap">
                        <span>Previous</span>
                    </div>
                </div>
                <div class="nextImg next">
                    <div class="tooltipWrap">
                        <span>Next</span>
                    </div>
                </div>

                <div class="alignL imgAlign">
                    <div class="tooltipWrap">
                        <span>Align left</span>
                    </div>
                </div>
                <div class="alignNone active imgAlign">
                    <div class="tooltipWrap">
                        <span>Fit to center</span>
                    </div>
                </div>
                <div class="alignR imgAlign">
                    <div class="tooltipWrap">
                        <span>Align right</span>
                    </div>
                </div>
            </div>
            <div class="img noimage">
                <span class="imageCount expresscurate_displayNone"></span>
            </div>
        </div>
        <div class="editor right">
          <?php wp_editor('', 'expresscurate_content_editor', $settings); ?>

          <div class="clear"></div>
        </div>
      </div>
      <div class="clear"></div>
      <div class="controls hidden">
        <ul class="expresscurate_preventTextSelection tags" id="expresscurate_special">

        </ul>
        <div class="clear"></div>
          <div id="expresscurate_slider" class="slide_container">
              <div class="slider">
                  <ul class="paragraphs_preview" id="curated_paragraphs">
                  </ul>
              </div>
              <div class="prevSlide inactiveButton"></div>
              <div class="nextSlide inactiveButton"></div>
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
      <button class="curate right" id="expresscurate_insert" onclick="return false;"><?php echo __('Curate into post', ExpressCurate_Actions::PLUGIN_FOLDER) ?></button>
      <div class="clear"></div>
    </div>
  </div>
</div>
<div id="expresscurate_loading">
  <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/loading.gif" id="img-load" />
</div>


