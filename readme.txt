=== WP EXCELTOPDF Converter Widget ===
Contributors: investintech
Donate link: none
Tags: pdf, converter, widget, web service, excel, xls
Requires at least: 3.0.1
Tested up to: 3.1
Stable tag: 0.1

Integrate EXCEL to PDF converter widgets, using Web Service, into any worpress website
 
== Description ==

This plugin will enable you to add EXCEL to PDF converter widgets to your sidebars.
New widget, called "WP EXCELTOPDF Converter Widget", is added. Each of these widgets can hold one EXCEL to PDF converter. For each widget/converter you will have option to control widgets skin and maximum file size accepted for input file.
Global Options page, "WP EXCEL to PDF Converter Widget", holds global skin and max file size settings, and affiliate ID field. Each skin have custom "skin.css" file with styles, using some custom templating tokens for element IDs.


*  You can add multiple converter widgets. 
*  Each widget can have different settings.
*  Each widget can have different CSS style/skin.
*  You can specify global settings that will apply for all widgets by default.
*  Affiliate links included, you can add your affiliate ID on global settings page. 
*  Widget settings will override global settings for WP EXCEL to PDF Converter.
*  Converter uses Investintech free Web Service to function.
*  Uploaded files are stored inside plugin directory and cleaned up using WP cron job each day


== Installation ==


1. Download the plugin zip file, and unzip the content.
2. Upload the content of downloaded zip file to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings->WP EXCEL to PDF Converter Widget page to select your global options and submit your Affiliate ID.
5. Go to Widgets page, and add your "WP EXCELTOPDF Converter Widget" widgets in any sidebar

== Frequently Asked Questions ==

= Conversion gets stuck on "Uploading file..." =

Make sure you have maximum file size setting defined.
Converter will not work on localhost servers, since web service is unable to access uploaded file. 

= How do I make new skin ? =

Create new folder in `/wp-content/plugins/wp-xlstopdf-widget/skins` and name it anyway you like your new skin to be called. Copy skin.css from default skin into newly created folder and edit the CSS rules. Add your images if you like.

= Browse button is not working on my website ? =

The plugin is using latest mootools javascript library, so please make sure you don't have mootools already included in your website, by some other plugin.


== Screenshots ==

1. Global options page
2. Global options page link
3. Widgets page
4. Front End look - idle
5. Front End look - file converted

== Changelog ==

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.1 =
* Initial release