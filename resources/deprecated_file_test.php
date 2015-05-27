<?php
// You can test the plugin by clicking the "Test" button on the admin screen
// (Plugins->Deprecated Calls)
_deprecated_file( basename( __FILE__ ), '2.5', 'some/new/file.php' );

function deprecated_function_test() {
	_deprecated_function( __FUNCTION__, '2.5', 'some_new_function()' );
}

function deprecated_argument_test() {
	_deprecated_argument( __FUNCTION__, '3.0', 'Use "some alternate method or new argument" instead.' );
}

?>
