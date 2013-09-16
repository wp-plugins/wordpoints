=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, cubepoints, credits
Requires at least: 3.6
Tested up to: 3.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reward your users' interactions with points.

== Description ==

This plugin lets you create one or multiple types of points which you can use to
reward your users by "hooking into" different user actions.

You can currently award points to users for...

* Registration
* Comments - You can also have points removed if you delete a user's comment or mark it as spam.
* Posts - You can be selective in which post types get awarded points, and award different amounts for different types. As with comments, you can have points removed when a post is deleted.
* Visiting your site - You can award points to a user when they visit your site at least once in a time period, once per day, for example.

All points transactions are logged and can be reviewed by administrators and
displayed on the front end of your site using the [`[wordpoints_points_logs]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can also display a list of the top users based on the number of points they have
using the [`[wordpoints_points_top]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/) shortcode.

The plugin also provides [several widgets](http://wordpoints.org/user-guide/widgets/) that you can use.

Many more features a planned in the near future, and you can check out the roadmap on
the plugin website, [WordPoints.org](http://wordpoints.org/roadmap/).

It is also possible to extend the default functionality of the plugin using modules.
For more information on that, see the [developer docs](http://wordpoints.org/developer-guide/).

== Installation ==

1. Download and unzip the plugin file
1. Upload the resulting `/wordpoints/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click on the WordPoints administration menu item
1. Click on the Components tab
1. Activate the Points component by clicking on the Activate button on the right
1. You can set up the points hooks to your liking by clicking on the Points Hooks submenu item

== Frequently Asked Questions ==

= Why does WordPoints have only one component? =

I plan to add more components in future, but right now these are still under
development. Find out more here.

= Does WordPoints support Multisite? =

No, the current version hasn't been tested with multisite. I plan to add support for
it soon, though.

= Why doesn't WordPoints support my old outdated WordPress version? =

Precisely because it is old, outdated, and most importantly, insecure. Backup and
upgrade now before it's too late. Seriously!

== Screenshots ==

1. An example of a table of points log entries.

2. The Points Hooks administration screen. This is where you configure when and where
points are awarded.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
* This is the initial release
