=== Log Deprecated Calls ===
A WordPress plugin by Stephen Rider
http://striderweb.com/nerdaphernalia/features/wp-log-deprecated-calls/
https://github.com/strider72/log-deprecated-calls
Contributors: strider72
Donate link: https://paypal.me/SteveRider
Tags: plugin, admin, developer, logging, debug, WP_DEBUG
Requires at least: 3.0
Tested up to: 4.6
Stable tag: 1.3
License: GPLv2 or later

Logs any calls to deprecated functions, files, or arguments, and identifies the function that made the call. Handy for plugin and theme authors.

== Description ==

Logs any calls to deprecated functions, files, or arguments, and identifies the function that made the call.  This should be very useful for plugin and theme authors who want to keep their code up-to-date with current WordPress standards.  Deprecated calls are logged via error_log() -- so they should show up in your regular PHP log file.

There is an admin screen under the "Plugins" menu.  There you can toggle logging to PHP log or a table in the WordPress Database.  You can also see records from that table (if any), and run a "test" that calls a dummy deprecated function, includes a dummy deprecated file, and calls a function with a deprecated argument.

== Screenshots ==

1.  The Manage page found under Plugins > Deprecated Calls

== Installation ==

Pretty much like any other WordPress Plugin.  Upload to Plugins directory.

Simply activate to record deprecated calls to your PHP log, or visit the Admin page under Plugins for other options.

== Frequently Asked Questions ==

= Does this work for themes too? =

Yes. It works for any code running within WordPress, including Themes and Plugins. You can even use it when testing patches for WordPress Core.

== Changelog ==

= 1.4 =
* 2016-04-04
* Now uses local private copy of Strider Core framework. Allows plugin hooks to load sooner and potentially catch more
* Deprecated call hooks also fire on higher priority (1)
* Removed scheduled purge code. Never really worked, and no longer needed after v1.3 improvements
* copied admin_footer() override to main file from Strider Core; removes Strider Core version on mouseover

= 1.3 =
* 2015-05-26
* Huge improvement to database efficiency. Rather than a new record for every call, non-unique calls are a single record with a counter.

= 1.2 =
* 2015-05-23
* Bugfix: Settings were not saving properly
* Now uses WordPress Settings API

= 1.1 =
* 2015-05-14
* Bugfix: blocker bug in backtrace function
* Bugfix: Saving settings in admin was unsetting some settings
* Some variable renames for clarity
* Changed strider_core string to _b2 to avoid breakage

= 0.6 =
* Added keys to $strider_core_plugins array
* Added menu icon.
* Changed Version Check URI
* switched to WP_PLUGIN_URL instead of WP_CONTENT_URL (dev8)

= 0.5.1 =
* Minor updates to Admin page

= 0.5 =
* Built on strider_core 0.1-dev
* Better organization of table in admin page -- grouped by calling file, line
* Bugfix in get_options() -- was running set_defaults() every time;
* Ozh Drop Down Menus icon
* Scheduled table purges turned off until I can figure them out better

= 0.4 =
* 11 August 2008
* Added uninstall.php (New WP 2.7 feature)
* Reworked log table a bit (field order)
* Strips ABSPATH or WP_CONTENT_DIR from paths when displaying table
* Normalized parentheses on function names
* Added some missing l18n calls in write_to_log()
* Added "Test" button to admin

= 0.3 =
* Admin page
	* Settings to toggle logging to PHP log/Database/both
	* Displays DB-logged calls
* Lots of code cleanup
* Scheduled table purges (not well tested)

= 0.2 =
* Now trims ABSPATH from paths (record to log only)
* Reworked test function
* Stuff for creating/updating table
* write_to_table function
* write_to_log function (0.1 repeated similar code 4 times...)
* "Nerdaphernalia standard" structure -- Class/ Admin polish/ set_defaults/ lots of abstraction

= 0.1 =
* July 29, 2008
* First public release

== Upgrade Notice ==

= 1.3 =
Huge increase in database efficiency. Highly recommended upgrade
