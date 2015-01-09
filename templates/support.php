<?php
global $current_user;
get_currentuserinfo();
$user_email = '';
if ($current_user->user_email) {
  $user_email = $current_user->user_email;
}
$msg_placeholder = 'Message';
if(isset($_GET['type']) && $_GET['type']=='keywords'){
    $msg_placeholder = 'Please, write your suggestions here ...';
}
$sent = false;
if ($_POST) {
    if ($_POST['expresscurate_support_email'] && $_POST['expresscurate_support_message']) {
        $expressCurateEmail = new ExpressCurate_Email();
        $sent =  $expressCurateEmail->sendSupportEmail($_POST['expresscurate_support_email'], 'Plugin feedback', $_POST['expresscurate_support_message']);
        unset($_POST);
    }
}
?>

<div class="expresscurate_support expresscurate_Styles wrap">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <h2>Support</h2>
        <!--<label>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sagittis nulla lectus,</label>-->
    </div>
  <h2 class="expresscurate_displayNone"> Support</h2>
  <div class="">
    <div class="block">
      <label class="publicRevolution">Join the public curating revolution and leave a feedback by email or twitter</label>

      <div>
        <a href="mailto:support@expresscurate.com" class="feedbackButton redBackground">email</a>
        <span>or</span>
        <a href="https://twitter.com/CurateSupport" target="_blank" class="feedbackButton blueBackground">twitter</a>
      </div>
      <label class="margin10">Like ExpressCurate tools & want to support us?</label>
      <a href="https://www.bit.ly/expresscuratedonate" target="_blank"
         class="donate">donate</a>
    </div>
    <div class="block">
      <?php if (!$sent) { ?>
        <label for="expresscurate_support_email">Leave your feedback</label>
        <?php
      } else {
        ?>
        <label for="expresscurate_support_email">Thanks for your feedback</label>
        <?php
      }
      ?>
        <form method="post" action=""
              id="expresscurate_support_form">
          <input id="expresscurate_support_email" name="expresscurate_support_email" class="inputStyle" placeholder="Email"
                 value="<?php echo $user_email ?>"/>
          <textarea class="inputStyle" name="expresscurate_support_message" id="expresscurate_support_message"
                    placeholder="<?php echo $msg_placeholder?>"></textarea>
          <a class="feedbackButton send greenBackground" href="#" onclick="Utils.expresscurateSupportSubmit();">Send</a>
        </form>
    </div>
  </div>
</div>
