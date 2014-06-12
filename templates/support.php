<?php
global $current_user;
get_currentuserinfo();
$user_email = '';
if ($current_user->user_email) {
  $user_email = $current_user->user_email;
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

<div class="expresscurate_support wrap">
    <div class="expresscurate_menu">
        <?php
        include(sprintf("%s/menu.php", dirname(__FILE__)));?>
    </div>
  <h2 class="expresscurate_displayNone"> Support</h2>
  <div class="">
    <div class="block">
      <label class="margin10">Join the public curating revolution and leave a feedback by email or twitter</label>

      <div>
        <a href="mailto:support@expresscurate.com" class="feedbackButton redBackground">email</a>
        <span>or</span>
        <a href="https://twitter.com/CurateSupport" target="_blank" class="feedbackButton blueBackground">twitter</a>
      </div>
      <label class="margin10">Like ExpressCurate tools & want to support us?</label>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7T6FC4B97CEH" target="_blank"
         class="donate">donate</a>
    </div>
    <div class="block">
      <?php if (!$sent) { ?>
        <label for="email">Leave your feedback</label>
        <?php
      } else {
        ?>
        <label for="email">Thanks for your feedback</label>
        <?php
      }
      ?>
        <form method="post" action="<?php echo get_admin_url() ?>admin.php?page=expresscurate"
              id="expresscurate_support_form">
          <input id="expresscurate_support_email" name="expresscurate_support_email" class="inputStyle" placeholder="Email"
                 value="<?php echo $user_email ?>"/>
          <textarea class="inputStyle" name="expresscurate_support_message" id="expresscurate_support_message"
                    placeholder="Message"></textarea>
          <a class="feedbackButton send greenBackground" href="#" onclick="expresscurate_support_submit();">Send</a>
        </form>
    </div>
  </div>
</div>
