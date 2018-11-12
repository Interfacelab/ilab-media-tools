<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Tools\Storage;

use WP_Error;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class ImgixImageEditor
 *
 * Replaces WordPress's WP_Image_Editor with one that supports all of the features
 * of Imgix.
 *
 */
class StorageImageEditor extends \WP_Image_Editor {
    /** @var \WP_Image_Editor The underlying image editor */
    private $imageEditor;
    private $attachmentId = null;

    public function __construct($file) {
        parent::__construct($file);

        $this->imageEditor = (\WP_Image_Editor_GD::test()) ? new \WP_Image_Editor_GD($file) : new \WP_Image_Editor_Imagick($file);

        if (isset($_POST['action']) && ($_POST['action'] == 'image-editor') && isset($_POST['do']) && ($_POST['do'] == 'save')) {
            if (isset($_POST['postid'])) {
                $this->attachmentId = $_POST['postid'];
                add_filter('ilab_should_override_attached_file', [$this, 'shouldOverrideAttachedFile'], 10000, 2);
                add_filter('ilab_ignore_existing_s3_data', [$this, 'shouldIgnoreExistingS3Data'], 10000, 2);
                add_filter('ilab_ignore_optimizers', [$this, 'shouldIgnoreOptimizers'], 10000, 2);
            }
        }
    }

    public function shouldOverrideAttachedFile($shouldOverride, $attachment_id) {
        remove_filter('ilab_should_override_attached_file', [$this, 'shouldOverrideAttachedFile']);

        if (!$shouldOverride) {
            return $shouldOverride;
        }

        if ($attachment_id == $this->attachmentId) {
            return false;
        }

        return true;
    }

    public function shouldIgnoreExistingS3Data($shouldIgnore, $attachment_id) {
        remove_filter('ilab_ignore_existing_s3_data', [$this, 'shouldIgnoreExistingS3Data']);

        if ($shouldIgnore) {
            return $shouldIgnore;
        }

        if ($attachment_id == $this->attachmentId) {
            return true;
        }

        return false;
    }

    public function shouldIgnoreOptimizers($shouldIgnore, $attachment_id) {
        if ($shouldIgnore) {
            return $shouldIgnore;
        }

        if ($attachment_id == $this->attachmentId) {
            return true;
        }

        return false;
    }

    /**
     * Loads image from $this->file into editor.
     *
     * @since 3.5.0
     *
     * @return bool|WP_Error True if loaded; WP_Error on failure.
     */
    public function load() {
        return $this->imageEditor->load();
    }

    public static function test( $args = array() ) {
        return true;
    }

    public static function supports_mime_type( $mime_type ) {
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
     *
     * @param  int|null $max_w Image width.
     * @param  int|null $max_h Image height.
     * @param  bool $crop
     * @return bool|WP_Error
     */
    public function resize($max_w, $max_h, $crop = false) {
        return $this->imageEditor->resize($max_w, $max_h, $crop);
    }

    /**
     * Resize multiple images from a single source.
     *
     * @since 3.5.0
     *
     * @param array $sizes {
     *     An array of image size arrays. Default sizes are 'small', 'medium', 'large'.
     *
     * @type array $size {
     * @type int $width Image width.
     * @type int $height Image height.
     * @type bool $crop Optional. Whether to crop the image. Default false.
     *     }
     * }
     * @return array An array of resized images metadata by size.
     */
    public function multi_resize($sizes) {
        return $this->imageEditor->multi_resize($sizes);
    }

    /**
     * Crops Image.
     *
     * @since 3.5.0
     *
     * @param int $src_x The start x position to crop from.
     * @param int $src_y The start y position to crop from.
     * @param int $src_w The width to crop.
     * @param int $src_h The height to crop.
     * @param int $dst_w Optional. The destination width.
     * @param int $dst_h Optional. The destination height.
     * @param bool $src_abs Optional. If the source crop points are absolute.
     * @return bool|WP_Error
     */
    public function crop($src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false) {
        return $this->imageEditor->crop($src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h, $src_abs);
    }

    /**
     * Rotates current image counter-clockwise by $angle.
     *
     * @since 3.5.0
     *
     * @param float $angle
     * @return bool|WP_Error
     */
    public function rotate($angle) {
        return $this->imageEditor->rotate($angle);
    }

    /**
     * Flips current image.
     *
     * @since 3.5.0
     *
     * @param bool $horz Flip along Horizontal Axis
     * @param bool $vert Flip along Vertical Axis
     * @return bool|WP_Error
     */
    public function flip($horz, $vert) {
        return $this->imageEditor->flip($horz, $vert);
    }

    public function get_size() {
        return $this->imageEditor->get_size();
    }

    /**
     * Streams current image to browser.
     *
     * @since 3.5.0
     *
     * @param string $mime_type The mime type of the image.
     * @return bool|WP_Error True on success, WP_Error object or false on failure.
     */
    public function stream($mime_type = null) {
        return $this->imageEditor->stream($mime_type);
    }

    public function save( $destfilename = null, $mime_type = null ) {
        $result = $this->imageEditor->save($destfilename, $mime_type);

        return $result;
    }
}