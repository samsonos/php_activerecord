<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 04.08.14 at 17:43
 */

// Define code sources path
define('SRC', __DIR__.'/');

/** Generic local module class autoloader */
function autoloader($className){
    // Try to find namespace separator
    if (($p = strrpos( $className, '\\' )) !== false ) {
        $className = substr( $className, $p + 1 );

        if(file_exists(SRC.$className.'.php')) {
            include(SRC.$className.'.php');
        }
    }
}

// Register custom old-style autoloader
spl_autoload_register('autoloader');

/** Set composer autoloader */
require 'global.php';

/** Set The Default Timezone */
date_default_timezone_set('UTC');
