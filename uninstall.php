<?php
// WordPress 2.7+ will call this file upon plugin Delete

if ( ! defined( 'ABSPATH' ) ) exit();	// sanity check

function uninstall_plugin_log_deprecated_calls() {
	global $wpdb;

	delete_option( 'plugin_log-deprecated_settings' );

	$table_name = $wpdb->prefix . 'deprecated_calls';	
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

	if ( wp_next_scheduled( 'purge_deprecated_calls_log' ) ) {
		wp_clear_scheduled_hook( 'purge_deprecated_calls_log' );
	}

}

uninstall_plugin_log_deprecated_calls();

?>