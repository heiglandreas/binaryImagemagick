=== binaryImagemagick ===
Contributors: heiglandreas
Tags: imagemagick, imagick, open_basedir
Requires at least: 4.1.0
Tested up to: 4.1.0
Stable tag: trunk

Use an Imagemagick-binary for image-manipulation

== Description ==

Add Imagemagic-Support for shared installations that have no Imagick-Extension
for PHP and have ```open_basedir```-restrictions on the folder containing the
```convert```-binary.

This plugin is enabled and will then be used as Image-Manipulation-library by
the default methods of wordpress.

Currently it expects the ```convert```-binary in either ```/bin``` or ```/usr/bin```.

== Installation ==

1. Upload the extracted folder `binaryImagemagick` to the `/wp-content/plugins/` directory or
search the plugin in your wordpress-installation
2. Activate the plugin through the 'Plugins' menu in WordPress

From now on all imagemanipulation will be done via the ImageMagick-binary

== Frequently Asked Questions ==

= Where can I find more Informations about the plugin? =

Go to https://github.com/heiglandreas/binaryImagemagick

= Where can I report issues with the plugin? =

Please use the issuetracker at https://github.com/heiglandreas/binaryImagemagick/issues

== Changelog ==
= 1.0.0 =
* Initial Release
