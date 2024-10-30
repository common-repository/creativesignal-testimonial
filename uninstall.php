<?php
/**
 @copyright  Copyright (C) 2012 creative-signal.com. All Rights Reserved.
 @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) exit ();

global $wpdb;
$table = $wpdb->prefix . 'creativesignal_testimonials';
$wpdb->query("DROP TABLE IF EXISTS $table");