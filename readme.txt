=== Arcade Ready ===
Contributors: bytephp
Tags: arcade, games, html5 embed, game script, embed games, gaming, game site, online games, casual games, embed flash, swf, casual games, html5 game, html5 arcade

Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn your blog into an arcade wonder! Add games directly into your other posts.

== Description ==

*ArcadeReady* will allow you to easily embed games and associated information into your existing posts and pages, making it a perfect tool for online game review sites or to just add some interactivity to your blog for your visitors to enjoy.

= Currently supported games formats and file types: =
* .swf (flash)
* HTML5 Games (embed code - javascript, iframe, etc.).
* Embed Code

*more coming soon*

== Installation ==

To install ArcadeReady,

1. Upload the plugin files to the `/wp-content/plugins/ArcadeReady` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

And you are set!


== Frequently Asked Questions ==

= The games I add doesn't show up in the games list. =

Make sure you click publish on the game, if you only save a draft, it will not appear in the games list.

= Will games I publish appear in search results when visitors use the search function on my website? =

No, the games are saved as a different post type that is excluded from searches and archives. They will only be visible in posts/pages where you embed them using shortcodes.

== Screenshots ==

1. Adding games to your game directory.
2. While editing your posts/pages click this button to bring up embed options.
3. Embed games and game information to an existing post/page.

== Changelog ==

= 1.1.1 =
*27.03.2017*

*Changes:*

* Added date field for games allowing you set a custom date the game was added.
* Added new shortcode: [ARgame game="ID" data="added"] to display date.

*Technical jibberish:*

* Added metabox: Date Added.
* Added datepicker type to metaHandler class.
* Added jQuery UI datepicker dependancy to AR core.js enqueuing.
* Fixed MetaHandler class throwing notice due to passing array instead of string.


= 1.1 =
*08.03.2017*

*Changes:*

* Added support for embedding Html5 games and other media requiring embed codes (iframe, script, etc.).

*Technical jibberish:*

* Added metabox: Embed Code.
* Added localization option for meta box texts.
* Added option to metaHandler class allowing base64 encoding of data.
* Updated load method of shortcode class to pass gameID along to getEmbedCode

= 1.0.2 =
*13.02.2017*

* Minor behind the scenes localization change to games post type

= 1.0.1 =
*13.02.2017*

* Updated the swf embed code.

= 1.0 =

* Initial version.

== Upgrade Notice ==

= 1.1 =
Added support for embedding Html5 games and other media requiring embed codes (iframe, script, etc.).
