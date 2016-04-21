<?php
/*
Plugin Name: Log Deprecated Calls
Plugin URI: http://striderweb.com/nerdaphernalia/features/wp-log-deprecated-calls/
Description: Logs any calls to deprecated functions, files, or function arguments, and identifies the function that made the call.  This should be very useful for plugin and theme authors who want to keep their code up-to-date with current WordPress standards.
Author: Stephen Rider
Author URI: http://striderweb.com/nerdaphernalia/
Version: 1.4
TextDomain: log_deprecated_calls
DomainPath: /lang/
*/

require( 'strider-core-local.php' );
require( 'log_deprecated_calls_main.php' );
?>