# binaryImagemagick

Add Imagemagic-Support for shared installations that have no Imagick-Extension
for PHP and have ```open_basedir```-restrictions on the folder containing the
```convert```-binary.

This plugin is enabled and will then be used as Image-Manipulation-library by
the default methods of wordpress.

Currently it expects the ```convert```-binary in either ```/bin``` or ```/usr/bin```.

