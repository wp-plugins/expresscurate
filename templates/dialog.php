<?php
$settings = array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'tinymce' => true, 'quicktags' => false);
$settingsClone = array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'tinymce' => true, 'quicktags' => false );
?>
<div>
    <div class="content_editor expresscurate_Styles" id="expresscurate_post_form">
        <div class="main">
            <div class="header">
                <div class="addressbar">
                    <input type="text" class="expresscurate_load"
                           placeholder="<?php echo __('Insert source URL to start', ExpressCurate_Actions::PLUGIN_FOLDER) ?>"
                           id="expresscurate_source" name="expresscurate_source"
                           value="<?php echo @get_post_meta($post->ID, 'expresscurate_source', true); ?>">
                    <input type="text" class="expresscurate_load"
                           placeholder="<?php echo __('Insert source URL to start', ExpressCurate_Actions::PLUGIN_FOLDER) ?>"
                           id="expresscurate_clone_source" name="expresscurate_source"
                           value="<?php echo @get_post_meta($post->ID, 'expresscurate_source', true); ?>">
                    <button class="load" id="expresscurate_submit">Load</button>
                    <button class="load expresscurate_displayNone" id="expresscurate_clone">Clone</button>
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

                        <div class="alignleft imgAlign">
                            <div class="tooltipWrap">
                                <span>Align left</span>
                            </div>
                        </div>
                        <div class="alignnone active imgAlign">
                            <div class="tooltipWrap">
                                <span>Fit to center</span>
                            </div>
                        </div>
                        <div class="alignright imgAlign">
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
                    <div id="expresscurate_dialog_content_editor_container">
                        <?php wp_editor('', 'expresscurate_dialog_content_editor', $settings); ?>
                    </div>
                    <div id="expresscurate_dialog_content_clone_editor_container" class="expresscurate_displayNone">
                        <?php wp_editor('', 'expresscurate_dialog_content_clone_editor', $settingsClone); ?>
                    </div>
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
            <input type="hidden" value="<?php echo get_option('expresscurate_curated_text', 'Curated from'); ?>"
                   id="expresscurate_from" name="expresscurate_from"/>
            <input type="hidden" value="<?php echo get_option('expresscurate_curated_link_target', 'on'); ?>"
                   id="expresscurate_from_target" name="expresscurate_from_target"/>
            <input type="hidden" value="<?php echo get_option('expresscurate_autosummary', 5); ?>"
                   id="expresscurate_autosummary" name="expresscurate_autosummary"/>
            <ul class="labels" id="curated_tags">
                <li class="markButton expresscurate_displayNone expresscurate_preventTextSelection">
                    <span>mark keywords</span></li>
            </ul>
            <div class="clear"></div>
            <button class="curate right" id="expresscurate_insert"
                    onclick="return false;"><?php echo __('Curate into post', ExpressCurate_Actions::PLUGIN_FOLDER) ?></button>
            <button class="curate right expresscurate_displayNone" id="expresscurate_cloneInsert"
                    onclick="return false;"><?php echo __('Clone Post', ExpressCurate_Actions::PLUGIN_FOLDER) ?></button>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div id="expresscurate_loading">
    <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/loading.gif" id="img-load"/>
</div>

<script type="text/html" id="tmpl-dialogCuratedImage">
    <li id="tcurated_image_{{data.index}}" class="tcurated_image" data-id="{{data.index}}"
        style="background-image: url({{data.url}})"></li>
</script>
<script type="text/html" id="tmpl-dialogCuratedParagraphs">
    <li id="tcurated_text_{{data.index}}" title="{{data.title}}" class="tcurated_text expresscurate_tag_{{data.tag}}"
        data-id="{{data.index}}">{{data.title}}
    </li>
</script>
<script type="text/html" id="tmpl-dialogCuratedtags">
    <li class="curated_post_tag" id="curated_post_tag_{{data.index}}"><span class="tag">{{data.tag}}</span><a href="#"
                                                                                                              class="remove"
                                                                                                              data-id="{{data.index}}"></a>
    </li>
</script>
<script type="text/html" id="tmpl-dialogMarkButton">
    <li class="markButton expresscurate_preventTextSelection"><span>mark keywords</span></li>
</script>
<script type="text/html" id="tmpl-dialogInsertTags">
    <li class="curated_post_tag" id="curated_post_tag_{{data.index}}"><a href="#" data-id="{{data.index}}">X</a><span>{{data.tag}}</span>
    </li>
</script>
<script type="text/html" id="tmpl-dialogSearchParagraphs">
    <li class="expresscurate_preventTextSelection expresscurate_dialog_shortPar expresscurate_shortParInactiveColor">
        <label>Short Paragraphs</label>
        <span class="shortPButton shortPButtonInactive"><span></span></span>
    </li>
    <li class="expresscurate_preventTextSelection expresscurate_dialog_search">
        <input class="expresscurate_disableInputStyle expresscurate_displayNone"/>
        <span class="close expresscurate_displayNone"></span>
        <span class="icon"></span>
    </li>
</script>
<script type="text/html" id="tmpl-dialogCuratedHeadings">
    <li class="curated_heading" id="curated_heading_{{data.index}}" data-tag="{{data.index}}" title="{{data.content}}">
        {{data.index}}
    </li>
</script>
<script type="text/html" id="tmpl-dialogCuratedDescription">
    <li id="curated_description" title="{{data}}">Description</li>
</script>
<script type="text/html" id="tmpl-socialPostDialog">
    <div class="expresscurate_socialDialog">
        <ul class="tabs">
            <li class="expresscurate_preventTextSelection facebook" data-tab="facebook"></li>
            <li class="expresscurate_preventTextSelection twitter" data-tab="twitter"></li>
            <li class="expresscurate_preventTextSelection youtube" data-tab="youtube"></li>
            <li class="expresscurate_preventTextSelection vimeo" data-tab="vimeo"></li>
        </ul>
        <div id="" class="tab-content">
            <textarea id="expresscurate_socialEmbed" placeholder="Embed code"
                      class="expresscurate_disableInputStyle"></textarea>
            <span class="expresscurate_errorMessage"></span>
        </div>
    </div>
</script>