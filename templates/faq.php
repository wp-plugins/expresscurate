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
    wp_mail('support@expresscurate.com', 'Expresscurate FAQ', $_POST['expresscurate_support_message']);
    $sent = true;
    unset($_POST);
  }
}
$feed = ExpressCurate_Settings::getCurationNews('http://news.expresscurate.com/category/faq/feed/');
$limit = 3;
?>


<div class="expresscurate_faq wrap">
  <div class="expresscurate_menu">
    <?php include(sprintf("%s/menu.php", dirname(__FILE__))); ?>
  </div>
  <h2 class="expresscurate_displayNone">FAQ</h2>

  <div>
    <div class="block questions">
      <?php
      if (count($feed) > 0) {
        for ($x = 0; $x < $limit; $x++) {
          $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
          $link = $feed[$x]['link'];
          ?>
          <div class="inlineBlock">
            <a class="questionBox" href="<?php echo $link ?>" target="_blank"><span><?php echo $title ?></span></a>
          </div>
          <?php
        }
        ?>
        <div class="inlineBlock">
          <a class="questionBox moreQuestions" href="http://news.expresscurate.com/category/faq/" target="_blank"><span>More questions</span></a>
        </div>
      <?php } else {
        ?>
        <div class="inlineBlock">
          <a class="questionBox moreQuestions" href="http://news.expresscurate.com/category/faq/" target="_blank"><span>Visit our faq blog</span></a>
        </div>
        <?php }
      ?>
    </div>
    <div class="block">
      <?php if (!$sent) { ?>
        <label for="email">Ask a question</label>
        <?php
      } else {
        ?>
        <label for="email">Your question has been sent</label>
        <?php
      }
      ?>
      <form method="post" action=""
            id="expresscurate_support_form">
        <input id="expresscurate_support_email" name="expresscurate_support_email" class="inputStyle"
               placeholder="Email"
               value="<?php echo $user_email ?>"/>
        <textarea class="inputStyle" name="expresscurate_support_message" id="expresscurate_support_message"
                  placeholder="Question"></textarea>
        <a class="askButton send greenBackground" href="#" onclick="FAQ.expresscurateSupportSubmit();">Ask</a>
      </form>
    </div>
  </div>
</div>
