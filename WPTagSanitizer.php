<?php

/*
  Plugin Name: WPTagSanitizer
  Plugin URI: https://github.com/larjen/WPTagSanitizer
  Description: A WordPress plugin that sanitizes tags in posts.
  Author: Lars Jensen
  Version: 1.0.1
  Author URI: http://exenova.dk/
 */

include_once(__DIR__ . DIRECTORY_SEPARATOR . "includes". DIRECTORY_SEPARATOR . "main.php");

if (is_admin()) {
    
    // include admin ui
    include_once(__DIR__ . DIRECTORY_SEPARATOR . "includes". DIRECTORY_SEPARATOR . "admin.php");

    // register activation and deactivation
    register_activation_hook(__FILE__, 'WPTagSanitizer::activation');
    register_deactivation_hook(__FILE__, 'WPTagSanitizer::deactivation');
    
}
