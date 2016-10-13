=== Website Tools by AddThis ===
Contributors: abramsm, jgrodel, bradaddthis.com, addthis_paul, addthis_matt, ribin_addthis, addthis_elsa, AddThis_Mike
Tags: Facebook, linkedin, pinterest, Share, sharing buttons, follow buttons, social marketing, social tools, instagram, slack, twitter, marketing tools, share buttons, widget, plugin, shortcode, website tools
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.1.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily link your WordPress site to access all AddThis tools, and control and edit easily through the AddThis dashboard.



== Description ==

The AddThis Website Tools WordPress plugin gives you control over how you activate and customize your AddThis tools. By simply installing this plugin through your WordPress site, you can make edits to your AddThis tools, such as share and follow buttons, all within the AddThis dashboard.

With AddThis Website Tools for WordPress, you can:

* Increase your email subscriptions, signups, and sales
* Get more social media shares, followers, and likes
* Customize the tools to match your brand’s aesthetic
* Get analytics to help you make informed decisions



== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'AddThis'
1. Click 'Install Now' and activate the plugin

For a manual installation via FTP:

1. Upload the addthis folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area

To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.



== Frequently Asked Questions ==

= Is AddThis free? =

Many of our tools are free, but Pro users get the benefit of exclusive widgets, including mobile­ friendly tools
and retina icons, priority support and deeper analytics.

= Do I need to create an account? =

Yes. This plugin requires an AddThis account. This plugin will walk you through creating an account on the plugin's registration page. It requires an email address, but that's it.

= Is JavaScript required? =

All AddThis website tools require javascript. JavaScript must be enabled. We load the actual interface via JavaScript at run-time, which allows us to upgrade the core functionality of the menu itself automatically everywhere whenever a new social sharing services comes out.

= Why use AddThis? =
1. Ease of use. AddThis is easy to install, customize and localize. We've worked hard to make a suite of simple and beautiful website tools on the internet.
1. Performance. The AddThis menu code is tiny and fast. We constantly optimize its behavior and design to make sharing a snap.
1. Peace of mind. AddThis gathers the best services on the internet so you don't have to, and backs them up with industrial strength analytics, code caching, active tech support and a thriving developer community.
1. Flexibility. AddThis can be customized via an API, and served securely via SSL. Share just about anything, anywhere ­­ your way.
1. Global reach. AddThis sends content to 200+ sharing services 60+ languages, to over 2 billion unique users in countries all over the world.

= What PHP version is required? =

This plugin requires PHP 5.2.4 or greater and is tested on the following versions of PHP:

* 5.2.4
* 5.2.17
* 5.3.29
* 5.4.45
* 5.5.34
* 5.6.20
* 7.0.5

= Who else uses AddThis? =
Over 15,000,000 sites have installed AddThis. With over 2 billion unique users, AddThis is helping share content all over the world, in more than sixty languages.

= What services does AddThis support? =
We currently support over 200 services, from email and blogging platforms to social networks and news aggregators, and we add new services every month. Want to know if your favorite service is supported? This list is accurate up to the minute: <a href="http://www.addthis.com/services">http://www.addthis.com/services</a>.

= Are there filters? =

Yes! There are lots of filters in this plugin.

Filters allow developers to hook into this plugin's functionality in upgrade-safe ways to define very specific behavior by writing their own PHP code snippets.

Developer <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.filters.md">documentation</a> on our filters is available.

= Are there widgets? =

Yes! There are widgets for available for all AddThis inline tools (the ones that don't float on the page).

If you register with an AddThis Pro account, you'll also see widgets for our Pro tools.

Developer <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.widgets.md">documentation</a> on our widgets is also available.

= Are there shortcodes? =

Yes! There are lots of shortcodes in this plugin. There are shortcodes for available for all AddThis inline tools (the ones that don't float on the page).

If you register with an AddThis Pro account, the shortcodes for our Pro tools will work for you, too.

See our <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.shortcodes.md">documentation</a> on our shortcodes.



== Screenshots ==

1. AddThis social share and follow tools on a page
2. Analytics on the AddThis Dashboard
3. Tool Gallery on the AddThis Dashboard
4. Customization options on the AddThis Dashboard
5. Home screen for an unregistered user
6. Home screen for a registered user
7. Advanced Settings screen
8. Adding widgets



== Changelog ==

= 1.1.2 =
* Error handling to prevent a PHP notice for an undefined property in AddThisFeature.php on line 704.
* Compatability with <a href="http://wordpress.org/extend/plugins/addthis-follow/">Follow Buttons by AddThis 3.0.0</a>, <a href="http://wordpress.org/extend/plugins/addthis-related-posts/">Related Posts by AddThis 1.0.0</a> & <a href="wordpress.org/support/plugin/addthis-smart-layers">Smart Layers by AddThis 2.0.0</a>

= 1.1.1 =
* Fix for JavaScript error in admin area: Cannot read property 'locale' of undefined
* Fix for PHP notices on AddThisTool.php on line 439 and AddThisPlugin.php on line 501 for special templates
* Bug fix for WordPress 3.5 and older (shortcode_exists is not defined)
* Speed improvements for all pages (x2 on the widget configuration page).

= 1.1.0 =
* New "AddThis Script" widget and addthis_script shortcode for troubleshooting around extra-pesky themes that aren't creating their headers or footers in the standard WordPress ways. If AddThis tools aren't showing up because the addthis_widget.js script isn't being included on your page, you can use this widget in a widget area, or add the shortcode onto any post or page.

= 1.0.2 =
* Eliminating various PHP Warnings and Notices

= 1.0.1 =
* Improved browser back/forward naviagtion support
* Added an incompatibility warning for users who had the <a href="https://wordpress.org/plugins/addthis/">Share Buttons by AddThis</a> plugin installed in WordPress mode
* First steps in internationalizing the plugin (with partial translation into Polish)

= 1.0.0 =
* Adds enabled AddThis sharing buttons onto the top and bottoms of post and page. At AddThis.com you can enable these buttons above and below posts, pages, and excerpts on the homepage, category pages and archives
* Adds horizontal recommeneded content tool below posts (when enabled on <a href="https://addthis.com">addthis.com</a>)
* Adds short codes for use inside posts for all inline tools.  <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.shortcodes.md">See documentation</a>.
* Includes widgets for all inline tools (register with a Pro account to see widgets for Pro tools). <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.widgets.md">See documentation</a>.
* Adds AddThis JavaScript onto your site with options for adding it asyncronously or syncronosly, as well as in the header or footer
* Walks existing AddThis users through logging into their AddThis account and picking a site profile to register their plugin without leaving WordPress. Once registered, AddThis is able to start collecting Analystics on your visitors social use of your site. No more copying in Profile IDs!
* Shares advanced options and registration (profile id) with the <a href="https://wordpress.org/plugins/addthis/">Share Buttons by AddThis</a> plugin (if installed).
* Include multiple ways of adding AddThis tools onto excerpts, and let's you turn them on and off so that you can tune this for how your theme accesses filters
* Many filters available for developers to hook into the plugin for advanced setups. <a href="https://plugins.svn.wordpress.org/addthis-all/trunk/documentation.filters.md">See documentation</a>.



== Upgrade Notice ==

= 1.1.2 =
Error handling to prevent a PHP notice for an undefined property in AddThisFeature.php on line 704. Compatability with <a href="http://wordpress.org/extend/plugins/addthis-follow/">Follow Buttons by AddThis 3.0.0</a>, <a href="http://wordpress.org/extend/plugins/addthis-related-posts/">Related Posts by AddThis 1.0.0</a> & <a href="wordpress.org/support/plugin/addthis-smart-layers">Smart Layers by AddThis 2.0.0</a>

= 1.1.1 =
Fix for JavaScript error in admin area: Cannot read property 'locale' of undefined. Fix for PHP notices on AddThisTool.php on line 439 and AddThisPlugin.php on line 501 for special templates. Bug fix for WordPress 3.5 and older (shortcode_exists is not defined). Speed improvements for all pages (x2 on the widget configuration page).

= 1.1.0 =
New "AddThis Script" widget and addthis_script shortcode for troubleshooting around extra-pesky themes that aren't creating their headers or footers in the standard WordPress ways. If AddThis tools aren't showing up because the addthis_widget.js script isn't being included on your page, you can use this widget in a widget area, or add the shortcode onto any post or page.

= 1.0.2 =
Eliminating various PHP Warnings and Notices

= 1.0.1 =
* Improved browser back/forward naviagtion support. First steps in internationalization. Added an incompatibility warning for users who had the <a href="https://wordpress.org/plugins/addthis/">Share Buttons by AddThis</a> plugin installed in WordPress mode.
