<?php
global $current_user;
get_currentuserinfo();
$user_email = '';
if ($current_user->user_email) {
    $user_email = $current_user->user_email;
}
if(isset($_GET['type']) && $_GET['type']=='keywords'){
    $msg_placeholder = 'Please, write your suggestions here ...';
}
$sent = false;
if ($_POST) {
    if ($_POST['expresscurate_support_email'] && $_POST['expresscurate_support_message']) {
        wp_mail('support@expresscurate.com', 'Plugin feedback', $_POST['expresscurate_support_message']);
        $sent = true;
        unset($_POST);
    }
}
?>
<div class="expresscurate_blocks expresscurate_Styles wrap">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>
        <h2>ExpressCurate</h2>
        <label></label>
    </div>
    <div class="expresscurate_blocksContainer">
        <div id ='keyWords' class="expresscurate_keywordsBlock item ">
            <label class="label">Keywords Summary</label>
            <?php $this->keywords_widget(); ?>
        </div>

        <?php
        if (get_option('expresscurate_publish', '') == "on") { ?>
            <div id="smartPublish" class="expresscurate_smartPublishBlock item">
                <label class="label">Smart Publishing Overview</label>
                <?php $this->smart_publishing_widget(); ?>
            </div>
        <?php } ?>

        <div id="feedWidget" class="expresscurate_feedBlock item">
            <label class="label">Feed</label>
            <?php $this->feed_widget();?>
        </div>

        <div id="bookmarks" class="expresscurate_bookmarksBlock item">
            <label class="label">Bookmarks</label>
            <?php $this->bookmarks_widget();?>
        </div>


        <div id ='support' class="expresscurate_supportBlock item ">
            <label class="label">Support</label>
            <div>
                <?php if (!$sent) { ?>
                    <label for="expresscurate_support_email">Leave your feedback</label>
                <?php
                } else {
                    ?>
                    <label for="expresscurate_support_email">Thanks for your feedback</label>
                <?php
                }
                ?>
                <form method="post" action="<?php echo get_admin_url() ?>admin.php?page=expresscurate"
                      id="expresscurate_support_form">
                    <input id="expresscurate_support_email" name="expresscurate_support_email" class="inputStyle" placeholder="Email"
                           value="<?php echo $user_email ?>"/>
                    <textarea class="inputStyle" name="expresscurate_support_message" id="expresscurate_support_message"
                              placeholder="Message"></textarea>
                    <a class="feedbackButton send greenBackground" href="#" onclick="Utils.expresscurateSupportSubmit();">Send</a>
                </form>
            </div>
        </div>

    </div>
</div>