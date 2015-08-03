=== BBP Auto-Close Topics ===
Contributors: thebrandonallen
Tags: forums, discussion, support, auto-close, close, topics, bbpress, bbp
Requires at least: 3.6
Tested up to: 4.2.3
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BBP Auto-Close Topics will automatically close bbPress topics after an admin-specified time period.

== Description ==

BBP Auto-Close Topics will automatically close bbPress topics after an admin-specified time period. The topic age is based on topic freshness. Topics are closed on-the-fly (rather than in the database), so, if the you deactivate the plugin, topics that were once closed will be re-opened.

Development of this plugin takes place on GitHub https://github.com/thebrandonallen/bbp-auto-close-topics. This is the preferred venue for support requests, and pull-requests are more than welcome :).

**\* Note:** bbPress must be installed and activated before this plugin will have any effect.

== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'BBP Auto-Close Topics'
3. Activate BBP Auto-Close Topics from your Plugins page.

= From WordPress.org =

1. Download BBP Auto-Close Topics.
2. Upload the 'bbp-auto-close-topics' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate BBP Auto-Close Topics from your Plugins page.

= Once Activated =

1. Visit 'Settings > Forums' and set the Auto-Close Topics setting under the 'Forum Settings' heading.

= Once Configured =

* Enjoy the magic show.

== Screenshots ==
1. Option in the bbPress Admin Interface

== Changelog ==

= 0.1.1 =
* Fix issue where languages other than English would auto-close all topics, no matter the time. Props mfiguerasma. See https://wordpress.org/support/topic/topics-always-closed-1

= 0.1.0 =
* Initial release.
