<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Util {

    public static function exec($command, &$output=NULL , &$return_var=NULL ) {
        $doesExecCommandExist = function_exists('exec');
        if($doesExecCommandExist) {
            return exec($command, $output, $return_var);
        }
        return false;
    }


    public static function tmpname($postfix = '.tmp', $prefix = 'tmp', $dir = null) {
        // validate arguments
        if (! (isset($postfix) && is_string($postfix))) {
            return false;
        }
        if (! (isset($prefix) && is_string($prefix))) {
            return false;
        }
        if (! isset($dir)) {
            $dir = getcwd();
        }

        // find a temporary name
        try {
            $sysFileName = tempnam($dir, $prefix);
            if ($sysFileName === false) {
                return false;
            }

            // tack on the extension
            $newFileName = $sysFileName . $postfix;
            if ($sysFileName == $newFileName) {
                return $sysFileName;
            }

            $newFileCreated =  @link($sysFileName, $newFileName);
            if ($newFileCreated) {
                return $newFileName;
            }

            unlink ($sysFileName);
        }catch (Exception $e){
            return false;
        }


        return false;
    }
}
