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


    public static function tmpname($prefix = 'tmp', $dir = null) {
        // validate arguments
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

            return $sysFileName;
        } catch (Exception $e) {
            return false;
        }
    }
}
