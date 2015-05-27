<?php
/*
Plugin Name: Log Deprecated Calls
Plugin URI: http://striderweb.com/nerdaphernalia/features/wp-log-deprecated-calls/
Description: Logs any calls to deprecated functions, files, or function arguments, and identifies the function that made the call.  This should be very useful for plugin and theme authors who want to keep their code up-to-date with current WordPress standards.
Author: Stephen Rider
Author URI: http://striderweb.com/nerdaphernalia/
Version: 1.3
Version Check URI: http://striderweb.com/wp-content/versions/log_deprecated.txt
TextDomain: log_deprecated_calls
DomainPath: /lang/
*/

$strider_core_b2_plugins[plugin_basename( __FILE__ )] = array(
	'core file' => dirname( __FILE__ ) . '/log_deprecated_calls_main.php',
	'name' => 'Log Deprecated Files'
	);

include_once( 'strider-core/strider-core.php' );
?>