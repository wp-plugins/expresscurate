<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="CONTENT-TYPE" content="text/html" charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpressCurate Content Notification</title>
    <style>
        @import url(http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,300,700);

        @media only screen and (min-width: 638px) {
            body[yahoo] .content {
                width: 637px !important;
            }
        }
    </style>
</head>
<body yahoo="fix" style="margin: 0; padding: 0;">
<table width="100%" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" class="wrapper"
       style="font-family: 'Open Sans', sans-serif;padding: 5px;">
    <tr style="font-family: 'Open Sans', sans-serif;">
        <td style="font-family: 'Open Sans', sans-serif;">
            <!--[if (gte mso 9)|(IE)]>
            <table width="637" align="center" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td>
            <![endif]-->
            <table class="content" align="center" cellpadding="0" cellspacing="0" border="0"
                   style="font-family: 'Open Sans', sans-serif;width: 100%;background-color: white;">
                <tr style="font-family: 'Open Sans', sans-serif;">
                    <td style="font-family: 'Open Sans', sans-serif;">
                        <table width="100%" class="borders"
                               style="font-family: 'Open Sans', sans-serif;padding: 2px;border-radius: 1px;border: 1px solid #e4e4e4;">
                            <tr style="font-family: 'Open Sans', sans-serif;">
                                <td class="header"
                                    style="font-family: 'Open Sans', sans-serif;background-color: #f0f0f0;padding: 17px 20px;">
                                    <a class="logo"
                                       style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;display: block;margin-right: 20px;float: left;height: 36px;width: 36px;border-radius: 18px;">
                                        <img src="http://grabicon.com/icon?domain=<?= get_bloginfo('url') ?>&origin=<?= get_bloginfo('url') ?>"
                                            style="font-family: 'Open Sans', sans-serif;">
                                        <!--<img src="http://www.expresscurate.com/images/email/content_notification/logo.png">-->
                                    </a>

                                    <div
                                        style="font-family: 'Open Sans', sans-serif;color: #68747b;font-size: 18px;font-weight: 200;font-style: italic;line-height: 36px;height: 36px;float: left;">
                                        Content Alert for <span class="blogName"
                                                                style="font-family: 'Open Sans', sans-serif;font-weight: 400;color: #333f47;"
                                                                title="<?= get_bloginfo('url') ?>"><?= get_bloginfo('domain'); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($emailData as $content) {
                                if (!empty($content['keywords'])) {
                                    ?>
                                    <tr style="font-family: 'Open Sans', sans-serif;">
                                        <td class="infoWrapper"
                                            style="font-family: 'Open Sans', sans-serif;padding: 0px 13px;">
                                            <table width="100%" class="infoTable"
                                                   style="font-family: 'Open Sans', sans-serif;">
                                                <tr style="font-family: 'Open Sans', sans-serif;">
                                                    <td class="title"
                                                        style="font-family: 'Open Sans', sans-serif;padding: 4px 0;padding-top: 10px;color: #45555f;font-weight: 600;font-size: 18px;">
                                                        <a href="<?= $content['link'] ?>"
                                                           style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;color: #45555f;"><?= $content['title'] ?></a>
                                                    </td>
                                                </tr>
                                                <tr style="font-family: 'Open Sans', sans-serif;">
                                                    <td class="info"
                                                        style="font-family: 'Open Sans', sans-serif;font-size: 14px;font-weight: 300;color: #9caeba;padding: 4px 0;">
                                                        <a href="#" class="blog"
                                                           style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;color: #20bc9d;"><?= $content['domain'] ?></a><?php if(isset($content['author']) && $content['author'] !='' ){ ?>
                                                        &nbsp; / &nbsp; by
                                                        <a href="#" class="author"
                                                           style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;color: #373d42;"><?= $content['author'] ?></a><?php } ?>
                                                        &nbsp; / &nbsp; <span class="clock"
                                                                              style="font-family: 'Open Sans', sans-serif;display: inline-block;height: 11px;width: 11px;"><img
                                                                src="http://www.expresscurate.com/p/images/email/content_notification/clock.png"
                                                                style="font-family: 'Open Sans', sans-serif;"></span>
                                                        &nbsp; <?= Date('F d, Y', strtotime($content['date'])) ?>
                                                    </td>
                                                </tr>
                                                <tr style="font-family: 'Open Sans', sans-serif;">
                                                    <td style="font-family: 'Open Sans', sans-serif;padding: 4px 0;">
                                                        <?php  foreach ($content['keywords'] as $keyword => $value) {
                                                            $color = '#3dc577'; // Green

                                                            if (round($value['percent'] * 100) < 3) {
                                                                $color = '#3598dc';
                                                            } else if (round($value['percent'] * 100) > 5) {
                                                                $color = '#e84c3d';
                                                            } ?>
                                                            <a href="#" class="inboundMarketing"
                                                               style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;display: inline-block;padding: 0px 9px;margin-right: 5px;text-align: center;height: 23px;font-size: 14px;font-weight: 400;line-height: 23px;color: white;background-color: <?= $color ?>;"><?= $keyword ?></a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>

                                            </table>
                                        </td>
                                    </tr>
                                <?php }
                            } ?>
                            <tr style="font-family: 'Open Sans', sans-serif;">
                                <td class="config"
                                    style="font-family: 'Open Sans', sans-serif;background-color: #f8f8f8;padding: 20px 20px;text-align: center;">
                                    <a href="<?= get_bloginfo('url') ?>/wp-admin/admin.php?page=expresscurate_settings"
                                       style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;color: #8a949e;">Configure
                                        alerts</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr style="font-family: 'Open Sans', sans-serif;">
                    <td style="font-family: 'Open Sans', sans-serif;">
                        <table width="100%" class="footer"
                               style="font-family: 'Open Sans', sans-serif;color: #8a949e;font-size: 13px;font-weight: 300;font-style: italic;padding: 5px 10px;">
                            <tr style="font-family: 'Open Sans', sans-serif;">
                                <td style="font-family: 'Open Sans', sans-serif;">
                                    &copy; Powered by <a href="http://www.expresscurate.com" class="expressFooter"
                                                         style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;color: #4cc5aa;">ExpressCurate</a>
                                </td>
                                <td width="130px" class="social" style="font-family: 'Open Sans', sans-serif;">
                                    <a href="https://www.facebook.com/expresscurate" class="fb"
                                       style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;display: inline-block;margin-left: 10px;height: 23px;width: 11px;"><img
                                            src="http://www.expresscurate.com/p/images/email/content_notification/fb.png"
                                            style="font-family: 'Open Sans', sans-serif;left: 0;"></a>
                                    <a href="https://twitter.com/ExpressCurate" class="tw"
                                       style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;display: inline-block;margin-left: 30px;height: 23px;width: 22px;"><img
                                            src="http://www.expresscurate.com/p/images/email/content_notification/tw.png"
                                            style="font-family: 'Open Sans', sans-serif;"></a>
                                    <a href="https://plus.google.com/+ExpresscurateToolkit/posts" class="gp"
                                       style="font-family: 'Open Sans', sans-serif;text-decoration: none;outline: none;display: inline-block;margin-left: 30px;height: 23px;width: 20px;"><img
                                            src="http://www.expresscurate.com/p/images/email/content_notification/gp.png"
                                            style="font-family: 'Open Sans', sans-serif;"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!--[if (gte mso 9)|(IE)]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </td>
    </tr>
</table>
</body>
</html>