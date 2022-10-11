<?php
/**
 * Plugin Name: WordPress Comments to Bark
 * Plugin URI: http://github.com/xqa/wordpress-comments2bark
 * Author: minge
 * Version: 1.1
 */

require_once dirname( __FILE__ ) . '/includes/class-settings-api.php';
require_once dirname( __FILE__ ) . '/includes/class-comments2bark.php';

new Comments2bark_Setting();