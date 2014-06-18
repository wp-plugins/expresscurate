<?php $page = $_GET['page'];
$page_arr = explode('_', $page);
$page = isset($page_arr[1])?$page_arr[1]:'';
?>
<div class="expresscurate_tabMenu">
    <span class="arrow"></span>
    <a class="<?php if($page=='keywords'){echo 'blue active';}?>" href="admin.php?page=expresscurate_keywords">Keywords</a>
    <a class="<?php if($page=='news'){echo 'blue active';}?>" href="admin.php?page=expresscurate_news">News</a>
    <a class="<?php if($page=='websites'){echo 'blue active';}?>" href="admin.php?page=expresscurate_websites">Top Sources</a>
    <a class="<?php if($page=='faq'){echo 'blue active';}?>" href="admin.php?page=expresscurate_faq">FAQ</a>
    <a class="<?php if($page==''){echo 'blue active';}?>" href="admin.php?page=expresscurate">Support</a>
    <a class="<?php if($page=='settings'){echo 'blue active';}?>" href="admin.php?page=expresscurate_settings">Settings</a>
</div>