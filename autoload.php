<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

function expresscurate_autoload($className) {
    $classFile =  sprintf("%s/$className", dirname(__FILE__));
    if (file_exists($classFile . '.php')) {
        require_once $className . '.php';
        return true;
    }
    return false;
}

spl_autoload_register('expresscurate_autoload');



/* ExpressCurate_Action */

//require_once 'ExpressCurate_Actions.php';
//require_once 'ExpressCurate_AjaxExportAPI.php';
//require_once 'ExpressCurate_API.php';
//require_once 'ExpressCurate_Keywords.php';
//require_once 'ExpressCurate_ContentManager.php';
//require_once 'ExpressCurate_Sitemap.php';
//require_once 'ExpressCurate_FeedManager.php';
//require_once 'ExpressCurate_Tags.php';
//require_once 'ExpressCurate_Email.php';
//require_once 'ExpressCurate_FeedManager.php';
//require_once 'ExpressCurate_CronManager.php';
//require_once 'ExpressCurate_Util.php';
//require_once 'ExpressCurate_HtmlParser.php';
//require_once 'ExpressCurate_Date.php';
//require_once 'ExpressCurate_GoogleClient.php';
//require_once 'ExpressCurate_SmartPublish.php';
//require_once 'ExpressCurate_Tracker.php';