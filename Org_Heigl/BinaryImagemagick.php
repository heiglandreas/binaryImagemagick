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
 */

namespace Org_Heigl;

require_once ABSPATH . WPINC . '/class-wp-image-editor.php';

use WP_Error;
use WP_Image_Editor;

class BinaryImagemagick extends WP_Image_Editor
{

    /**
     * @var string The path tho the convert-binary
     */
    protected static $convert = null;

    /**
     * @var array $options The options to pass to convert
     */
    protected $options = array();

    /**
     * @var string $image The path to the image to be changed
     */
    protected $image = null;

    public static function applyFilters($value)
    {
        array_unshift($value, '\Org_Heigl\BinaryImagemagick');

        return $value;
    }

    /**
     * Add an ImageMagick-Option for the 'convert'-command
     *
     * @param string $option
     * @param string $value
     *
     * @see http://www.imagemagick.org/script/convert.php#options
     * @return self
     */
    public function addOption($option, $value)
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Remove an option
     *
     * @param string $option The option to remove
     *
     * @return self
     */
    public function removeOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            unset($this->options[$option]);
        }

        return $this;
    }

    /**
     * Get the option-string for the convert-command
     *
     * @return string
     */
    public function getOptionString()
    {
        $opts = array();

        foreach ($this->options as $key => $val)
        {
            $opts[] = '-' . $key . ' ' . $val;
        }

        return implode(' ', $opts);
    }


    /**
     * Checks to see if current environment supports ImageMagick.
     *
     * @return boolean
     */
    public static function test($args = array())
    {

        // First, test Imagick's extension and classes.
        exec('which convert', $content, $result);
        if (0 == $result) {
            self::$convert = $content[0];

            return true;
        }

        exec('export PATH=/usr/bin:/usr/local/bin; which convert', $content, $result);
        if (0 == $result) {
            self::$convert = $content[0];
            return true;
        }

        return false;
    }

    /**
     * Checks to see if editor supports the mime-type specified.
     *
     * @param string $mime_type
     * @return boolean
     */
    public static function supports_mime_type($mime_type)
    {
        $imagick_extension = strtoupper( self::get_extension( $mime_type ) );

        if ( ! $imagick_extension )
            return false;

        exec(self::$convert . ' -list format | grep -i ' . $imagick_extension, $result);
        if (count($result) > 0) {

            return true;
        }

        return false;
    }

    /**
     * Loads image from $this->file
     *
     * @return boolean|WP_Error True if loaded; WP_Error on failure.
     */
    public function load()
    {

        if ( ! is_file( $this->file ) && ! preg_match( '|^https?://|', $this->file ) ) {
            return new WP_Error('error_loading_image', __('File doesn&#8217;t exist?'), $this->file);
        }

        /** This filter is documented in wp-includes/class-wp-image-editor-imagick.php */
        // Even though Imagick uses less PHP memory than GD, set higher limit for users that have low PHP.ini limits
        @ini_set( 'memory_limit', apply_filters( 'image_memory_limit', WP_MAX_MEMORY_LIMIT ) );

        $this->image = $this->file;

        $this->update_size();

        return true;
    }

    /**
     * Resizes current image.
     *
     * At minimum, either a height or width must be provided.
     * If one of the two is set to null, the resize will
     * maintain aspect ratio according to the provided dimension.
     *
     * @since 3.5.0
     * @access public
     *
     * @param  int|null $max_w Image width.
     * @param  int|null $max_h Image height.
     * @param  boolean  $crop
     * @return boolean|WP_Error
     */
    public function resize( $max_w, $max_h, $crop = false )
    {
        if (($this->size['width'] == $max_w) && ($this->size['height'] == $max_h)) {

            return true;
        }

        $dims = image_resize_dimensions($this->size['width'], $this->size['height'], $max_w, $max_h, $crop);
        if (! $dims) {

            return new WP_Error('error_getting_dimensions', __('Could not calculate resized image dimensions'));
        }


        list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

        if ($crop) {

            return $this->crop($src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h);
        }

        $this->addOption('resize ', $dst_w . 'x' . $dst_h);

        return $this->update_size($dst_w, $dst_h);
    }

    /**
     * Resize multiple images from a single source.
     *
     * @param array $sizes {
     *     An array of image size arrays. Default sizes are 'small', 'medium', 'large'.
     *
     *     Either a height or width must be provided.
     *     If one of the two is set to null, the resize will
     *     maintain aspect ratio according to the provided dimension.
     *
     *     @type array $size {
     *         @type int  ['width']  Optional. Image width.
     *         @type int  ['height'] Optional. Image height.
     *         @type bool $crop   Optional. Whether to crop the image. Default false.
     *     }
     * }
     * @return array An array of resized images' metadata by size.
     */
    public function multi_resize($sizes)
    {
        $metadata = array();
        foreach ( $sizes as $size => $size_data ) {
            if (!isset($size_data['width']) && !isset($size_data['height'])) {
                continue;
            }

            if (!isset($size_data['width'])) {
                $size_data['width'] = null;
            }
            if (!isset($size_data['height'])) {
                $size_data['height'] = null;
            }

            if (!isset($size_data['crop'])) {
                $size_data['crop'] = false;
            }

            $this->resize($size_data['width'], $size_data['height'], $size_data['crop']);

            $this->_save();
        }

        return $metadata;
    }

    /**
     * Crops Image.
     *
     * @since 3.5.0
     * @access public
     *
     * @param string|int $src The source file or Attachment ID.
     * @param int $src_x The start x position to crop from.
     * @param int $src_y The start y position to crop from.
     * @param int $src_w The width to crop.
     * @param int $src_h The height to crop.
     * @param int $dst_w Optional. The destination width.
     * @param int $dst_h Optional. The destination height.
     * @param boolean $src_abs Optional. If the source crop points are absolute.
     * @return boolean|WP_Error
     */
    public function crop($src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false)
    {
        if ($src_abs) {
            $src_w -= $src_x;
            $src_h -= $src_y;
        }

        $this->addOption('crop', $src_w . 'x' . $src_h . '+' . $src_x . 'x' . $src_y);

        if ($dst_w || $dst_h) {
            // If destination width/height isn't specified, use same as
            // width/height from source.
            if ( ! $dst_w )
                $dst_w = $src_w;
            if ( ! $dst_h )
                $dst_h = $src_h;

            $this->addOption('resize', $dst_w . 'x' . $dst_h);

        }
        return $this->update_size();
    }

    /**
     * Rotates current image counter-clockwise by $angle.
     *
     * @since 3.5.0
     * @access public
     *
     * @param float $angle
     * @return boolean|WP_Error
     */
    public function rotate($angle)
    {
        /**
         * $angle is 360-$angle because Imagick rotates clockwise
         * (GD rotates counter-clockwise)
         */
        $this->addOption('rotate', 360 - $angle);

        $result = $this->update_size();
        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Flips current image.
     *
     * @since 3.5.0
     * @access public
     *
     * @param boolean $horz Flip along Horizontal Axis
     * @param boolean $vert Flip along Vertical Axis
     * @returns boolean|WP_Error
     */
    public function flip($horz, $vert)
    {
        if ($horz) {
            $this->addOption('flip', null);
        }
        if ($vert) {
            $this->addOption('flop', NULL);
        }

        return true;
    }

    /**
     * Saves current image to file.
     *
     * @since 3.5.0
     * @access public
     *
     * @param string $destfilename
     * @param string $mime_type
     * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
     */
    public function save( $destfilename = null, $mime_type = null )
    {
        $saved = $this->_save($destfilename, $mime_type);

        return $saved;
    }

    /**
     * Do the actual conversion!
     *
     * @param string $filename The new filename
     * @param string $mime_type
     *
     * @return array
     */
    protected function _save($filename = null, $mime_type = null )
    {
        list($filename, $extension, $mime_type) = $this->get_output_format($filename, $mime_type);

        if (! $filename)
            $filename = $this->generate_filename(null, null, $extension);

        try {

            $this->make_image($filename, array($this, 'writeImage'), array($filename) );

        }
        catch ( Exception $e ) {
            return new WP_Error( 'image_save_error', $e->getMessage(), $filename );
        }

        // Set correct file permissions
        $stat = stat( dirname( $filename ) );
        $perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
        @ chmod( $filename, $perms );

        /** This filter is documented in wp-includes/class-wp-image-editor-gd.php */
        return array(
            'path'      => $filename,
            'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
            'width'     => $this->size['width'],
            'height'    => $this->size['height'],
            'mime-type' => $mime_type,
        );
    }

    /**
     * Sets or updates current image size.
     *
     * @since 3.5.0
     * @access protected
     *
     * @param int $width
     * @param int $height
     */
    protected function update_size( $width = null, $height = null ) {
        $size = null;
        if ( !$width || !$height ) {
            try {
                exec(self::$convert . ' -identify "' . $this->file . '" null', $result);
                foreach ($result as $line) {
                    if (! preg_match('/\s(\d+)x(\d+)\s/i', $line, $values)) {
                        continue;
                    }
                    $size = array('width' => $values[1], 'height' => $values[2]);
                    break;
                }
            }
            catch ( Exception $e ) {
                return new WP_Error( 'invalid_image', __('Could not read image size'), $this->file );
            }
        }

        if ( ! $width )
            $width = $size['width'];

        if ( ! $height )
            $height = $size['height'];

        return parent::update_size( $width, $height );
    }


    public function writeImage($filename)
    {
        exec(self::$convert . ' ' . $this->file . ' ' . $this->getOptionString() . ' ' . $filename, $result);

        return $this;
    }

    /**
     * Streams current image to browser.
     *
     * @since 3.5.0
     * @access public
     *
     * @param string $mime_type
     * @return boolean|WP_Error
     */
    public function stream($mime_type = null)
    {
        // Removes bug introduced by MediaLibraryAssistant
        // Due to the here removed filter the convert tries to create a "jpe"
        // file which it doesn't know about.
        if (has_filter('mime_types', 'MLAMime::mla_mime_types_filter')) {
            remove_filter('mime_types', 'MLAMime::mla_mime_types_filter', 0x7FFFFFFF);
        }
        list( $filename, $extension, $mime_type ) = $this->get_output_format(null, $mime_type );

        header( "Content-Type: $mime_type" );
        error_log(self::$convert . ' "' . $this->file . '" ' . $this->getOptionString() . ' ' .  $extension . ':-'  );
        passthru(self::$convert . ' "' . $this->file . '" ' . $this->getOptionString() . ' ' .  $extension . ':-', $result);

        return true;
    }
}