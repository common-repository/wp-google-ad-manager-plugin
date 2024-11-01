=== WP Google Ad Manager Plugin ===
Contributors: Andreas Berger
Donate link: http://www.bretteleben.de/
Tags: Google Ad Manager, Google Adsense, Ad Server, WordPress Plugin
Requires at least: 2.7.0
Tested up to: 2.8.5
Stable tag: 1.1.0

This plugin allows to use Google Ad Manager to display Ads within your posts, pages and sidebar.


== Description ==
The Plugin allows to set your Ad Manager Publisher ID + up to ten fields with Ad Manager Slotnames and to call them from within your template, from within sidebar widgets and from within posts.
Further it comes with full support for custom targeting attributes, page-level attributes and slot-level attributes.

For information and updates, please visit:
http://www.bretteleben.de

= Usage =

After having set PublisherID and AdSlotNames at "Settings" > "Google Ad Manager" in your Administration Interface put the calls:  

1) in your Template:
To call ads from within your template (above or below content, at a fixed position in your sidebar, and so on ...) insert the call directly into the sourcecode of the template.
The call comes as HTML-comment and looks such as:
<!-- begam{field_to_use} -->
where "field_to_use" is one of the fields above: S0 - S9, so - to call field S1, the call would look such as: <!-- begam{S1} -->

2) in your Posts:
To call ads from within a post, switch to HTML-view and insert the call at the position, you want the ad to be displayed within your post.
The call comes as HTML-comment and looks such as:
<!-- begam{field_to_use} -->
where "field_to_use" is one of the fields above: S0 - S9, so - to call field S7, the call would look such as: <!-- begam{S7} -->
You may want to use the fields 7 to 9 with your posts, because they allow to set more than one slot. How many slots you set in these fields is up to you. Set 3 slots and the first three posts will display the ads. Set 5, 7, 10 ... exactly. :)

3) in your Sidebar:
To place ads in your sidebar, ad a Text-widget (for "Arbitrary text or HTML" - it comes with wordpress) to your sidebar and put into it the call, which again looks such as:
<!-- begam{field_to_use} -->
where "field_to_use" is one of the fields above: S0 - S9, so - to call field S3, the call would look such as: <!-- begam{S3} -->


= Ad Manager Attributs =

The plugin supports additional Attributs. These are - optional - added to the call in two additional sections, separated by forward slashes "/".
The complete Call may consist of up to three sections:
<!-- begam{field_to_use/targeting_attributs/page_attributs,slot_attributs} -->

1) Custom Targeting - GA_googleAddAttr(key, value)

The optional second section may contain custom Ad Manager target-attributes that influence ad selection.
For further information on these parameters please refer to the Google Ad Manager Help.
A custom target-attribute always consists of two parts, the attribute and the value.
They are declared following the scheme: Attribute "comma" Value. E.g.: Gender,Male
To use more than one attribute, separate them with a semicolon ";" E.g.: Gender,Male;AgeRange,18To24
The complete call using custom target-attributes may look such as:
<!-- begam{field_to_use/Gender,Male;AgeRange,18To24} -->

2) Page Attributs and Slot Attributes - GA_googleAddAdSensePageAttr(param, value), GA_googleAddAdSenseSlotAttr(slotname, param, value)

The optional second section may contain Ad Manager page-level attributes and/or Ad Manager slot-level attributes.
For further information on these parameters please refer to the Google Ad Manager Help.

2.1) A custom page-level attribute always consists of two parts, the parameter and the value.
They are declared following the scheme: Parameter "comma" Value. E.g.: google_color_bg,FF0000
To use more than one attribute, separate them with a semicolon ";" E.g.: google_color_bg,FF0000;google_color_link,00FF00
The complete call using custom target-attributes and page-level attributes may look such as:
<!-- begam{field_to_use/Gender,Male;AgeRange,18To24/google_color_bg,FF0000;google_color_link,00FF00} -->

2.2) A custom slot-level attribute always consists of three parts, the slot, the parameter and the value.
They are declared following the scheme: Slot (represented by "S") "comma" Parameter "comma" Value. E.g.: S,google_color_bg,FF0000
To use more than one attribute, separate them with a semicolon ";" E.g.: S,google_color_bg,FF0000;S,google_color_link,00FF00
(Notice: The "S" is replaced by the plugin with the corresponding Slotname automatically.)
The complete call using custom target-attributes, page-level attributes and slot-level-attributes may look such as:
<!-- begam{field_to_use/Gender,Male;AgeRange,18To24/google_color_bg,FF0000;S,google_color_link,00FF00} -->

3) IMPORTANT!
To use page-level or slot-level attributes without using target-attributes you have to declare an empty second section, otherwise the plugin will not be able to execute the call correctly.
In this case, the first section (field_to_use) is followed by 2 forward slashes!
The complete call using page-level and slot-level attributes but no custom target-attributes may look such as:
<!-- begam{field_to_use//google_color_bg,FF0000;S,google_color_link,00FF00} -->

== Installation ==

1. Unzip and Upload the folder 'wp-google-ad-manager-plugin' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options.
4. Add the calls <!-- begam{field_name} --> to a post, page or widget where u want it to appear.

== Screenshots ==

== Frequently Asked Questions ==

For further questions visit the [(a)WP Google Ad Manager Plugin](http://www.bretteleben.de/lang-en/wordpress/google-anzeigenmanager-plugin.html) Homepage

== Version history ==

= Version 1.1.0 =
* Use register options for WP MU compatibility

= Version 1.0.0 =
* Support for custom targeting added
* Support for page-attributes added
* Support for slot-attributes added
* Initial JavaScript-Tags updated
* Changes in readme.txt

= Version 1.0.0beta =
* Initial release version