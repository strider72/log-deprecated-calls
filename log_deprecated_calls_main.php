<?php
// TODO: Make sure table format updates automatically

class log_dep_calls extends strider_core_b2_LogDeprecatedCalls {

	var $option_name = 'plugin_log-deprecated-calls_settings';
	var $option_version = '0.4.1';
	var $option_bools = array ( 'to_log', 'to_table' );
	var $text_domain = 'log_deprecated_calls';

	var $table_name = 'deprecated_calls';
	var $table_version = '1.2';

	function __construct() {
		global $wpdb;
	// first set variables
		$this->plugin_file = dirname( __FILE__ ) . '/log_deprecated_calls.php';
		//$this->menu_icon_url = WP_PLUGIN_URL . '/' . basename( dirname( $this->plugin_file ) ) . '/resources/menu_icon.svg'; // optional

		$this->table_name = $wpdb->prefix . $this->table_name;
		$this->setup_log_table();

	// then run "core" functions
		$this->core_init();

	// then whatever else this plugin needs
		add_action( 'deprecated_function_run', array(&$this, 'deprecated_function'), 1, 3 );
		add_action( 'deprecated_file_included', array(&$this, 'deprecated_file'), 1, 3 );
		add_action( 'deprecated_argument_run', array(&$this, 'deprecated_argument'), 1, 3 );
		add_action( 'admin_menu', array( &$this, 'add_admin_page' ) );

	}

	function get_default_options( $mode = 'merge', $curr_options = null ) {
		// $mode can also be set to "reset"
		$def_options = array(
			'last_opts_ver' => $this->option_version,
			'last_table_ver' => $this->table_version,
			'to_log' => true,
			'to_table' => false );
		return $this->_get_default_options( $def_options, $mode, $curr_options );
	}

//*********************************
//    Call Handling
//*********************************

	function deprecated_function( $function, $replacement = null, $version = 'N/A' ) {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 5 );
		$target = $backtrace[4]['function'];
		$caller = $backtrace[4]['file'];
		$line_num = $backtrace[4]['line'];

		return $this->handle_call( $target, 'function', $caller, $line_num, $replacement, $version );
	}

	function deprecated_file( $file, $replacement = null, $version = 'N/A' ) {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 5 );
		$target = $backtrace[3]['file'];
		$caller = $backtrace[4]['file'];
		$line_num = $backtrace[4]['line'];

		return $this->handle_call( $target, 'file', $caller, $line_num, $replacement, $version );
	}

	function deprecated_argument( $function, $message = null, $version = 'N/A' ) {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 5 );
		$target = $backtrace[4]['function'];
		$caller = $backtrace[4]['file'];
		$line_num = $backtrace[4]['line'];

		return $this->handle_call( $target, 'argument', $caller, $line_num, $message, $version );
	}

	function handle_call( $target, $context, $caller, $line_num, $replacement, $version ) {
		$options = $this->get_options();
		if ( $options['to_log'] )
			$this->write_to_log( $target, $context, $caller, $line_num, $replacement, $version );
		if ( $options['to_table'] )
			$this->write_to_table( $target, $context, $caller, $line_num, $replacement, $version );
		return true;
	}

	function write_to_log( $target, $type, $caller, $line_num, $replacement = null, $version = null ) {
		if ( ! $replacement ) {
			$replacement = __( 'No alternative is listed.', $this->text_domain );
		} else {
			if ( substr( $replacement, -2 ) == '()' )
				$replacement = substr_replace( $replacement, '', -2 );
		}
		$call = ( $type == 'file' ) ? __( 'included', $this->text_domain ) : __( 'called', $this->text_domain );
		$version = ($version == 'N/A') ? '' : " (since WP $version)";

		switch( $type ) {
			case 'file' :
			case 'function' :
				$replacement = sprintf( __( 'Use "%1$s" instead.', $this->text_domain ), $replacement );
				$error_message = sprintf( __( 'WordPress: The ***deprecated %1$s***%2$s "%3$s" was %4$s from %5$s on line %6$s. %7$s', $this->text_domain ), $type, $version, $target, $call, $caller, $line_num, $replacement );
				break;
			case 'argument' :
				$error_message = sprintf( __( 'WordPress: A ***deprecated %1$s***%2$s was used in a call to the function "%3$s", which was %4$s from %5$s on line %6$s. %7$s', $this->text_domain ), $type, $version, $target, $call, $caller, $line_num, $replacement );
				break;
		}
		$error_message = str_replace( ABSPATH, '', $error_message );
		error_log( $error_message );
	}

	function write_to_table( $target, $type, $caller, $line_num, $replacement = null, $version = '' ) {
		// NOTE: Records entire server path; Admin page removes it before display
		if ( ! $this->log_table_exists() )
			$this->setup_log_table();

		if ( substr( $replacement, -2 ) == '()' ) {
			$replacement = substr_replace( $replacement, '', -2 );
		}

		global $wpdb;

		$dbwhere = $wpdb->prepare(
			"WHERE call_type=%s AND target=%s AND replacement=%s AND calling_file=%s AND line_num=%d",
			$type,
			$target,
			$replacement,
			$caller,
			$line_num
		);
		$dbsearch = "SELECT * FROM $this->table_name " . $dbwhere;
		$results = $wpdb->query( $dbsearch );

		if( $results == 0 ) {
			$dbquery  = $wpdb->prepare(
				"INSERT INTO $this->table_name (call_time, call_type, target, replacement, calling_file, line_num, version, counter)
				VALUES ( %d, %s, %s, %s, %s, %d, %s, %d )",
				time(),
				$type,
				$target,
				$replacement,
				$caller,
				$line_num,
				$version,
				1
			);
		} else {
			$timenow = time();
			$dbquery = "UPDATE $this->table_name SET counter = counter + 1, call_time = $timenow " . $dbwhere;
		}
		$results = $wpdb->query( $dbquery );
		return $results;
	}

	function test() {
		include_once( dirname( __FILE__ ) . '/resources/deprecated_file_test.php' );
		deprecated_function_test();
		deprecated_argument_test();
	}

//*********************************
//    DB Table Functions
//*********************************

	function setup_log_table() {

		$options = $this->get_options();
		if ( $options['last_table_ver'] != $this->table_version ) {
			global $wpdb;
			$sql = "DROP TABLE IF EXISTS $this->table_name;";
			$wpdb->query($sql);
			$this->update_option( 'last_table_ver', $this->table_version );
		}


		if ( ! $this->log_table_exists() ) {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $this->table_name (
				call_time bigint(11) DEFAULT '0' NOT NULL,
				call_type tinytext NOT NULL,
				target tinytext NOT NULL,
				replacement tinytext,
				calling_file tinytext NOT NULL,
				line_num int NOT NULL,
				version tinytext,
				counter int UNSIGNED
				) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	function log_table_exists() {
		global $wpdb;
		return ( $wpdb->get_var( "SHOW TABLES LIKE '$this->table_name'" ) == $this->table_name );
	}

	function purge_log_table() {
		global $wpdb;
		$delquery = "TRUNCATE TABLE $this->table_name";
		return $wpdb->query( $delquery );
	}

//*********************************
//    Settings Page Code
//*********************************

	function add_admin_page() {
		$param = array( 'plugins.php', __( 'Deprecated Call Log', $this->text_domain ), __( 'Deprecated Calls', $this->text_domain ), 'manage_options', 'deprecated', 'admin_page' );
		return $this->_add_admin_page( $param );
	}

	function admin_footer() {
		$pluginfo = $this->get_plugin_data();
		printf( $this->sc__( '%1$s plugin | Version %2$s | by %3$s<br />' ),
		        $pluginfo[ 'Title' ], $pluginfo[ 'Version' ],
		        $pluginfo[ 'Author' ] );
	}

	function register_settings () {
		register_setting( $this->text_domain . '-group', $this->option_name, array(&$this, 'process_options' ) );
	}

	function process_options ( $input ) {
		$input['to_log'] = wp_validate_boolean( $input['to_log'] );
		$input['to_table'] = wp_validate_boolean( $input['to_table'] );
		$input = $this->get_default_options( 'merge', $input );
		return $input;
	}

	function filter_plugin_actions( $links, $file ){
		$param = func_get_args();
		return $this->_filter_plugin_actions( $param, $this->admin_link, __( 'Manage' ) );
	}

	function add_ozh_adminmenu_icon( $hook ) {
		$param = func_get_args();
		return $this->_add_ozh_adminmenu_icon( $param, 'deprecated' );
	}

	function process_secondary_forms() {
		if ( isset( $_POST['purge_table'] ) ) {
			wp_verify_nonce( $this->text_domain . '-purge-table' );
			$this->purge_log_table();
			echo '<div id="message" class="updated fade"><p><strong>' . __( 'Records Purged.', $this->text_domain ) . '</strong></p></div>';
		} else if ( isset( $_POST['test_deprecated'] ) ) {
			wp_verify_nonce( $this->text_domain . '-test' );
			$this->test();
			echo '<div id="message" class="updated fade"><p><strong>' . __( 'Test Complete.', $this->text_domain ) . '</strong></p></div>';
		}
		return true;
	}

	function admin_page() {
		add_action( 'in_admin_footer', array( &$this, 'admin_footer' ), 9 );
		$this->process_secondary_forms();
?>
<div class="wrap">
	<h2><?php _e( 'Deprecated Call Log Settings', $this->text_domain ); ?></h2>
	<form action="options.php" method="post">
		<?php
		settings_fields( $this->text_domain . '-group' );
		$options = get_option( $this->option_name );
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Logging</th>
					<td><label for="to_log"><input type="checkbox" name="<?php echo $this->option_name; ?>[to_log]" id="to_log" <?php echo( checked( $options['to_log'] ) ); ?>/> <?php _e( 'Record calls in PHP log', $this->text_domain ); ?></label><br />
					<label for="to_table"><input type="checkbox" name="<?php echo $this->option_name; ?>[to_table]" id="to_table" <?php echo( checked( $options['to_table'] ) ); ?>/> <?php _e( 'Record calls in Database', $this->text_domain ); ?></label><br />
					<?php _e( '(Recording to Database will display calls on this page.)', $this->text_domain ); ?></td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="save_settings" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" /></p>
	</form>

<?php

		if ( $this->log_table_exists() ) {
			global $wpdb;
			$query = "SELECT DISTINCT target, call_type, calling_file, line_num, replacement, version
					  FROM $this->table_name 
					  ORDER BY calling_file, line_num";
			$result = $wpdb->get_results( $query, ARRAY_A );
		} else {
			$result = array();
		}
		// echo "<pre>Schedules:\n " . print_r(wp_get_schedules(), true) . "\n\nCrons:\n" . print_r(_get_cron_array(), true) . '</pre>'

		if ( $options['to_table'] || count( $result ) > 0 ) {
?>
	<h2><?php _e( 'Deprecated Call Log', $this->text_domain ); ?></h2>
	<form action="plugins.php?page=deprecated" method="post">
		<?php if( count( $result ) > 0 ) { ?>
		<input type="submit" name="purge_table" class="button-secondary" value="<?php _e( 'Purge Records', $this->text_domain ) ?>" />
		<?php }
			if ( function_exists( 'wp_nonce_field' ) )
			wp_nonce_field( $this->text_domain . '-purge-table' );
		?>
		<table border="0" id="dep_calls_log">
<?php
			if( count( $result ) > 0 ) {
				//$alt_row = false;
				$count = 1;
				$last_caller = '';
				foreach ( $result as $record ) {
					if ( defined('WP_CONTENT_DIR') )
						$strip_path = dirname( WP_CONTENT_DIR ) . '/';
					else
						$strip_path = ABSPATH;
					$record['target'] = str_replace( $strip_path, '', $record['target'] );
					$record['calling_file'] = str_replace( $strip_path, '', $record['calling_file'] );

					switch( $record['call_type'] ) {
						case 'function' :
							$record['target'] .= '()';
							$record['replacement'] .= '()';
							break;
						case 'argument' :
							$record['target'] .= '()';
							break;
					}

					$record['call_type'] = ucwords($record['call_type']);
					if ( $record['call_type'] == 'Argument' )
						$record['call_type'] .= ' in';
					if ( $record['calling_file'] != $last_caller ) {
						$grouphead = "<tr style=\"font-size: 120%\"><th colspan=\"3\" style=\"height: 2em; text-align: left; vertical-align: bottom;\"><span style=\"font-family: Courier, Courier New, Monaco, monospace;\">{$record['calling_file']}</span></th></tr>";
						// $count = 1;
						$alt_row = true;
					} else {
						$grouphead = '';
					}
					$rowclass = $alt_row ? ' style="background-color: #DDD"' : '';
					echo <<<EOS
		{$grouphead}
		<tr{$rowclass}>
			<td rowspan="4">{$count}</td>
			<th style="text-align: right;">{$record['call_type']}</th><td style="padding-left: 0.5em;"><span style="font-family: Courier, Courier New, Monaco, monospace;">{$record['target']}</span></td></tr>
		<tr{$rowclass}>
			<th style="text-align: right;">Replaced with</th><td style="padding-left: 0.5em;"><span style="font-family: Courier, Courier New, Monaco,  monospace;">{$record['replacement']}</span></td></tr>
		<tr{$rowclass}>
			<th style="text-align: right;">Called from</th><td style="padding-left: 0.5em;">line <span style="font-family: Courier, Courier New, Monaco, monospace;">{$record['line_num']}</span></td></tr>
		<tr{$rowclass}>
			<th style="text-align: right;">Deprecated Since</th><td style="padding-left: 0.5em;"><span style="font-family: Courier, Courier New, Monaco,  monospace;">{$record['version']}</span></td></tr>
EOS;
					$alt_row = $alt_row ? false : true;
					$last_caller = $record['calling_file'];
					++$count;
				}
			} else {
				echo "\n<tr><td>" . __( 'No deprecated calls recorded.', $this->text_domain ) . "</td></tr>\n";
			}
			echo '
			</table>
	</form>
';
		} // end if 
?>
	<br />
	<h2><?php _e( 'Test', $this->text_domain ); ?></h2>
	<form action="plugins.php?page=deprecated" method="post">
		<?php if ( function_exists( 'wp_nonce_field' ) )
			wp_nonce_field( $this->text_domain . '-test' );
		?>
		<p><input type="submit" name="test_deprecated" class="button-primary" value="<?php _e( 'Run Test', $this->text_domain ) ?>" /> <?php _e( 'This will call a "dummy" deprecated function, file, and argument, and log them as normal.', $this->text_domain )?></p>
	</form>
</div><!-- wrap -->
<?php
	}

} // end class log_dep_calls

$log_dep_calls = new log_dep_calls;

?>