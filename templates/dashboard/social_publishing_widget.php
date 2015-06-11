<?php
$buffer = new ExpressCurate_BufferClient();
$profiles = $buffer->getProfiles();
?>

<div class="expresscurate_dashboard expresscurate_Styles expresscurate_dashboard_social_publishing">
    <div>
        <ul class="list">
            <?php
            if (!empty($profiles)) {
                $profilesStatus = array();
                if (get_option('expresscurate_social_publishing_profiles', '')) {
                    $profilesStatus = json_decode(stripslashes(urldecode(get_option('expresscurate_social_publishing_profiles', ''))));
                }
                foreach ($profiles as $i => $profile) {
                    $profileId = $profile->id;

                    if ($profilesStatus->$profileId == 'on' || empty($profilesStatus->$profileId)) {
                        $counts = $profile->counts;
                        $formatedService = (strpos($profile->formatted_service, 'Google') !== false) ? 'Google' : $profile->formatted_service;
                        ?>
                        <li class="expresscurate_background_wrap">
                            <div
                                class="expresscurate_social_box expresscurate_social_box_<?php echo $formatedService; ?>">
                                <span class="text"><?php echo $profile->formatted_username ?></span>
                                <span class="dailySuggestions"><span
                                        class="tooltip">daily suggestions</span><?php echo $counts->daily_suggestions; ?></span>
                            </div>
                            <div title="" class="statistics borderRight">
                                <div class="center">sent<br/><span><?php echo $counts->sent; ?></span></div>
                            </div>
                            <div title="" class="statistics borderRight">
                                <div>pending<span><?php echo $counts->pending; ?></span></div>
                            </div>
                            <div title="" class="statistics">
                                <div>drafts<span><?php echo $counts->drafts; ?></span></div>
                            </div>
                        </li>
                    <?php }

                }
            } else {
                ?>
                <label class="expresscurate_notDefined">There is no social profile in <a
                        href="tbd: the link to the buffer account">your Buffer account</a>.</label>
            <?php } ?>

        </ul>
    </div>
    <a class="settingsLink" href="admin.php?page=expresscurate_settings">Settings</a>
    <a class="settingsLink" href="edit.php?post_status=draft&post_type=post">Drafted Posts</a>
</div>