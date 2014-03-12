<div class="wrap">
  <h2><?php echo ExpressCurate_Settings::PLUGIN_NAME ?> Settings</h2>
  <form method="post" action="options.php"> 
    <?php @settings_fields('expresscurate-group'); ?>
    <?php @do_settings_fields('expresscurate-group'); ?>

    <?php //do_settings_sections('expresscurate'); ?>
    <table class="form-table">

      <tr valign="top">
        <th scope="row" colspan="2">
          <strong>Default Category for Curated Posts</strong>
          <br>
          <span class="gray-italic">Please pick a default category for your curated posts from the list below. This will prevent a default "Uncategorized" being assigned to your post automatically.</span>
        </th>
      </tr>
      <tr valign="top">
        <td class="with-padding" colspan="2">
          <table>
            <tr valign="top">
              <?php
              $categories = get_categories(array("hide_empty" => 0));
              $cat_i = 1;
              ?><td><?php
                foreach ($categories as $category) {
                  ?>
                  <input type="radio" id="expresscurate_cat_<?php echo $category->term_id; ?>" value="<?php echo $category->term_id; ?>" name="expresscurate_def_cat" <?php
                  if (get_option('expresscurate_def_cat') == $category->term_id) {
                    echo 'checked="checked"';
                  }
                  ?>>					
                  <label for="expresscurate_cat_<?php echo $category->term_id; ?>"> <?php echo $category->name; ?> </label><br />
                  <?php if ($cat_i % 6 == 0 && count($categories) != $cat_i) {
                    ?>
                  </td>
                  <td>
                  <?php }
                  ?>
                  <?php
                  $cat_i++;
                  ?><?php
                }
                ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" colspan="2">
          <strong>Attribution Text For Original Article Link</strong>
          <br>
          <span scope="row" colspan="2" class="gray-italic">Type in the prefix text for attributing the original article link.  It will show up at the bottom of your curated post.  For example, if you type "Curated from" in the box below and you curate from google.com, "Curated from google.com" will appear at the bottom of your post.</span>
        </th>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <input type="text" class="wide-input with-max-width" id="expresscurate_curated_text" value="<?php
          if (get_option('expresscurate_curated_text')) {
            echo get_option('expresscurate_curated_text');
          } else {
            echo 'Curated from';
          }
          ?>" name="expresscurate_curated_text" size="50" />
        </td>
      </tr>
      <tr valign="top">
        <td scope="row" rowspan="2" class="width-for-td">
          <strong>Publishing Directly from ExpressCurate Chrome Extension:</strong>
          <br>
          <span scope="row" class="gray-italic">This setting gives you an option to save your curated post as a draft or publish it when you click on the "Curate" button on ExpressCurate Chrome extension.</span>
        </td>
        <td>
          <input type="radio" id="expresscurate_post_published" value="publish" name="expresscurate_post_status" <?php
          if (get_option('expresscurate_post_status', '') == "publish") {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_post_published"> Published </label>
          <input type="radio" id="expresscurate_post_draft" value="draft" name="expresscurate_post_status" <?php
          if (get_option('expresscurate_post_status', '') == "draft" || get_option('expresscurate_post_status', '') == '') {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_post_draft"> Draft </label><br>
        </td>
      </tr>
      <tr class="width-bottom-border">
        <td colspan="2">
          &nbsp;
        </td>
      </tr>
      <tr valign="top" class="width-bottom-border">
        <td scope="row" class="width-for-td">
          <strong>Image Placement:</strong>
          <br>
          <span scope="row" class="gray-italic">
            There are two options for placing the images (picked from the original article) in your curated post:<br/>
            <ol>
              <li>Featured. The image will be placed above the title, at the very top of your post. This will give your image a more prominent look.
              <li>Standard. The image will be placed below the title and category. This option will focus your viewer's attention more on the title and less on the image.
            </ol>
          </span>
        </td>
        <td>
          <input type="radio" id="expresscurate_featured" value="1" name="expresscurate_featured" <?php
          if (get_option('expresscurate_featured', '') == "1" || get_option('expresscurate_featured', '') == '') {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_featured"> Featured </label>
          <input type="radio" id="expresscurate_featured_no" value="0" name="expresscurate_featured" <?php
          if (get_option('expresscurate_featured', '') == "0") {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_featured_no"> Standard </label>
        </td>
      </tr>
      <tr valign="top" class="width-bottom-border">
        <th scope="row" class="width-for-td">
          <strong>Max Number of Auto-suggested Tags:</strong>
          <br>
          <span class="gray-italic">ExpressCurate can auto-suggest tags for your post. It is based on the keywords and tags defined in your prior WordPress blogs. Enter the max number of auto-suggested tags you would like to have in each curated posts.</span>
        </th>
        <td>
          <input type="text" id="expresscurate_max_tags" class="centered-text" value="<?php
          if (get_option('expresscurate_max_tags')) {
            echo get_option('expresscurate_max_tags');
          } else {
            echo '3';
          }
          ?>" name="expresscurate_max_tags" size="1" />
          <span class="gray-italic">&nbsp;&nbsp; The recommended value is 3</span>
        </td>
      </tr>
      <tr valign="top" class="width-bottom-border">
        <th scope="row" class="width-for-td">
          <strong>Number of Curated Paragraphs:</strong>
          <br>
          <span class="gray-italic">Pick the number of paragraphs to be inserted from the original article into your post.</span>
        </th>
        <td>
          <input type="text" id="expresscurate_autosummary" class="centered-text" value="<?php
          if (get_option('expresscurate_autosummary')) {
            echo get_option('expresscurate_autosummary');
          } else {
            echo '5';
          }
          ?>" name="expresscurate_autosummary" size="1" /><span class="gray-italic">&nbsp;&nbsp; The default value is 5</span>
        </td>
      </tr>
      <tr valign="top" class="width-bottom-border">
        <td scope="row" class="width-for-td">
          <strong>SEO Enhancements:</strong>
          <br>
          <span scope="row" class="gray-italic">
            Click on "Yes" to enable ExpressCurate SEO enhancements for your curated post.
          </span>
        </td>
        <td>
          <input type="radio" id="expresscurate_seo" value="1" name="expresscurate_seo" <?php
          if (get_option('expresscurate_seo') == "1" || get_option('expresscurate_seo', '') == '') {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_seo"> Yes </label>
          <input type="radio" id="expresscurate_seo_no" value="0" name="expresscurate_seo" <?php
          if (get_option('expresscurate_seo', '') == "0") {
            echo 'checked="checked"';
          }
          ?> />
          <label for="expresscurate_seo_no"> No </label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" colspan="2">
          <strong>Your Keyword Tags:</strong>
          <br>
          <span class="gray-italic">
            Enter your target keywords that you want to be tagged in the post.  Multiple keywords need to be separated by commas.  
            <br/>When this list is defined, ExpressCurate will look for these words in curated content and try to tag them in the article, as well as create links from these keywords that show up on tag's page.</span>
        </th>
      </tr>




      <tr valign="top">
        <td  colspan="2">
          <textarea id="expresscurate_defined_tags" class="with-max-width" name="expresscurate_defined_tags" cols="20" rows="3" /><?php
          if (get_option('expresscurate_defined_tags')) {
            echo get_option('expresscurate_defined_tags');
          } else {
            echo '';
          }
          ?></textarea>
        </td>
      <tr>  


    </table>
    <?php @submit_button(); ?>
  </form>
</div>