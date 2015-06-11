<?php
global $post;
$buffer = new ExpressCurate_BufferClient();
$profiles = $buffer->getProfiles();

$profilesStatus = array();
if (get_option('expresscurate_social_publishing_profiles', '')) {
    $profilesStatus = json_decode(stripslashes(urldecode(get_option('expresscurate_social_publishing_profiles', ''))));
}

$publishedPosts = get_post_meta($post->ID, '_expresscurate_social_published_post_messages', true);
$posts = get_post_meta($post->ID, '_expresscurate_social_post_messages', true);
?>
<div class="expresscurate_social_post_widget">
    <input id="expresscurate_postId" type="hidden" value="<?php echo $post->ID; ?>"/>

    <input type="hidden" id="expresscurate_social_post_messages" name="expresscurate_social_post_messages"
           value="<?php echo htmlspecialchars(json_encode($posts), ENT_QUOTES); ?>"/>


    <ul class="mainControls">
        <li id="expresscurate_addTweet" class="expresscurate_social_widget_buttons">New</li>
        <li class="expresscurate_social_widget_buttons expresscurate_social_get_content">Get content</li>
        <li data-header="h1" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H1</li>
        <li data-header="h2" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H2</li>
        <li data-header="h3" class="expresscurate_headerTweet expresscurate_social_widget_buttons">H3</li>

        <li id="expresscurate_socialTitlePost" class="expresscurate_social_widget_buttons">Social Title</li>
        <li id="expresscurate_socialDescriptionPost" class="expresscurate_social_widget_buttons">Social Description</li>
        <li id="expresscurate_socialShortDescriptionPost" class="expresscurate_social_widget_buttons">Social Short
            Description
        </li>
    </ul>

    <?php
    if (!empty($posts)) {
        foreach ($posts as $i => $data) {
            $postLengthCount = ($data['postLength']);
            $approved = $data['approved']; ?>
            <div class="expresscurate_socialPostBlock" id="<?php echo $data['id']; ?>">
                <ul class="topControls">

                    <?php if (!empty($profiles)) { ?>
                        <select name="profile" id="profile" <?php if ($approved) echo 'disabled="disabled"' ?> >
                            <?php
                            foreach ($profiles as $profile) {
                                $profileId = $profile->id;
                                if ($profilesStatus->$profileId == 'on' || empty($profilesStatus->$profileId)) {
                                    ?>
                                    <option <?php
                                    if ($profileId == $data['profile_ids']) {
                                        echo 'selected="selected"';
                                    }
                                    ?> value="<?php echo $profileId; ?>"><?php echo $profile->formatted_service; ?>
                                        / <?php echo $profile->formatted_username; ?></option>
                                <?php }
                            } ?>
                        </select>
                    <?php } ?>

                    <li class="close expresscurate_floatRight <?php if ($approved) echo 'expresscurate_displayNone' ?> "></li>
                    <div class="expresscurate_clear"></div>
                </ul>
                <textarea name=""
                          class="expresscurate_social_post_content expresscurate_disableInputStyle" <?php if ($approved) echo 'readonly="readonly"' ?>
                          id=""><?php echo $data['message']; ?></textarea>
                <ul class="bottomControls">
                    <li class="expresscurate_social_widget_buttons expresscurate_floatRight <?php if ($approved) echo 'expresscurate_displayNone' ?> approve">
                        Approve
                    </li>
                    <li class="expresscurate_social_widget_buttons expresscurate_floatRight <?php if ($approved) echo 'expresscurate_displayNone' ?> clone">
                        Copy
                    </li>
                    <li class="expresscurate_socialPostLength <?php if ($approved) echo 'expresscurate_displayNone' ?> expresscurate_floatRight <?php if ($postLengthCount < 0) echo 'error'; ?>"><?php echo $postLengthCount; ?></li>
                    <li class="expresscurate_social_widget_buttons <?php if (!$approved) echo 'expresscurate_displayNone' ?> edit">
                        Edit
                    </li>
                    <div class="expresscurate_clear"></div>
                </ul>
            </div>
        <?php }
    }

    if (!empty($publishedPosts)) {
        foreach ($publishedPosts as $i => $data) {
            ?>
            <div class="expresscurate_socialPostBlock" id="<?php echo $data['id']; ?>">
                <ul class="topControls">
                    <li><?php echo $data['formatted_username']; ?></li>
                </ul>
                <span class="expresscurate_social_post_content_published"><?php echo $data['message']; ?></span>
                <ul class="bottomControls">
                    <li class="expresscurate_socialPostLength expresscurate_floatRight">Scheduled</li>
                    <div class="expresscurate_clear"></div>
                </ul>
            </div>
        <?php }
    }
    ?>
</div>

<script type="text/html" id="tmpl-socialPostWidget">
    <div class="expresscurate_socialPostBlock" id="{{data.id}}">
        <ul class="topControls">
            <?php if (!empty($profiles)) { ?>
                <select name="profile" id="profile">
                    <?php
                    foreach ($profiles as $profile) {
                        $profileId = $profile->id;
                        if ($profilesStatus->$profileId == 'on' || empty($profilesStatus->$profileId)) {
                            ?>
                            <option value="<?php echo $profileId; ?>"><?php echo $profile->formatted_service; ?>
                                / <?php echo $profile->formatted_username; ?></option>
                        <?php }
                    } ?>
                </select>
            <?php } ?>
            <li class="close expresscurate_floatRight"></li>
            <div class="expresscurate_clear"></div>
        </ul>
        <textarea name="" class="expresscurate_social_post_content expresscurate_disableInputStyle"
                  id="">{{data.message}}</textarea>
        <ul class="bottomControls">
            <li class="expresscurate_social_widget_buttons approve expresscurate_floatRight">Approve</li>
            <li class="expresscurate_social_widget_buttons clone expresscurate_floatRight">Copy</li>
            <li class="expresscurate_socialPostLength expresscurate_floatRight {{data.errorColor}}">
                {{data.postLength}}
            </li>
            <li class="expresscurate_social_widget_buttons expresscurate_displayNone edit">Edit</li>
            <div class="expresscurate_clear"></div>
        </ul>
    </div>
</script>