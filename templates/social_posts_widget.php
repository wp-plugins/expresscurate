<?php
$buffer = new ExpressCurate_BufferClient();
$profiles = $buffer->getProfiles();
?>
<div class="expresscurate_social_post_widget">
    <ul class="mainControls">
        <li id="expresscurate_addTweet" class="expresscurate_social_widget_buttons">Create</li>
        <li class="expresscurate_social_widget_buttons expresscurate_social_get_content">Get content</li>
        <li data-header="h1" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H1</li>
        <li data-header="h2" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H2</li>
        <li data-header="h3" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H3</li>
    </ul>
    <input id="expresscurate_postId" type="hidden" value="<?php echo the_ID(); ?>"/>

</div>

<script type="text/html" id="tmpl-socialPostWidget">
    <div class="expresscurate_tweetBlock">
        <ul class="topControls">
            <li class="close expresscurate_floatRight" ></li>
            <div class="expresscurate_clear"></div>
        </ul>
        <textarea name="" class="expresscurate_social_post_content expresscurate_disableInputStyle" id="">{{data}}</textarea>
        <ul class="bottomControls">
            <li class="expresscurate_social_widget_buttons approve">Approve</li>
        </ul>
    </div>
</script>