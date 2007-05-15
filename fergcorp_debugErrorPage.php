<?php
/*
Plugin Name: Dunstan-style Error Page Debug
Plugin Description: WARNING: Activating this plugin will reset all settings associated with Dunstan-style Error Page!
Version: 0.2
Author: Andrew Ferguson
Author URI: http://www.fergcorp.com/

Copyright (c) 2007 Andrew Ferguson. All Rights Reserved.

*/

add_action('activate_fergcorp_debugErrorPage.php', 'fergcorp_debugErrorPage_install');

function fergcorp_debugErrorPage_install(){
global $wpdb;
		$wpVersion = "Version: ".get_bloginfo('version')."\n\n";
		$optionsDump = "Options Dump:\n".$wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE `option_name` = 'afdn_error_page'");
		mail("andrew@fergcorp.com", "[Error Page Debug Report]", $wpVersion.$optionsDump);
		$wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` = 'afdn_error_page'");

}
?>
