<?php
/**
 * Copyright (c)2015-2015 heiglandreas
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIBILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category 
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright ©2015-2015 Andreas Heigl
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @since     29.01.15
 * @link      https://github.com/heiglandreas/
 *
 * Plugin Name: binaryImageMagick
 * Plugin URI: https://wordpress.org/plugins/binaryImagemagick/
 * Description: Add a binary ImageMagick-Provider
 * Author: Andreas Heigl<andreas@heigl.org>
 * Author URI: https://github.com/heiglandreas/binaryImagemagick
 * Version: 1.0.0
 * Text Domain: binaryImagemagick
 */
require_once __DIR__ . '/Org_Heigl/BinaryImagemagick.php';

add_filter('wp_image_editors',array('\Org_Heigl\BinaryImagemagick', 'applyFilters'));