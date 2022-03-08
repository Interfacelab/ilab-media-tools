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

namespace MediaCloud\Plugin\Tools\Imgix;

use MediaCloud\Plugin\Tools\ImageSizes\ImageSizePrivacy;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tools\DynamicImages\DynamicImagesTool;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Wizard\ConfiguresWizard;
use MediaCloud\Plugin\Wizard\WizardBuilder;
use MediaCloud\Vendor\FasterImage\FasterImage;
use MediaCloud\Vendor\Imgix\UrlBuilder;
use function MediaCloud\Plugin\Utilities\anyIsSet;
use function MediaCloud\Plugin\Utilities\arrayContainsAny;
use function MediaCloud\Plugin\Utilities\arrayPath;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}


/**
 * Class ImgixTool
 *
 * Imgix tool.
 */
class ImgixTool extends DynamicImagesTool implements ConfiguresWizard {

    //region Constructor
    public function __construct($toolName, $toolInfo, $toolManager) {
	    $this->settings = ImgixToolSettings::instance();

	    parent::__construct($toolName, $toolInfo, $toolManager);

        add_filter('media-cloud/imgix/enabled', function($enabled){
            return $this->forcedEnabled || $this->enabled();
        });

	    add_filter('media-cloud/imgix/alternative-formats/enabled', function($enabled){
	        return ($this->forcedEnabled || $this->enabled() && $this->settings->enabledAlternativeFormats);
        });
    }
    //endregion

	//region Tool Overrides
	public function enabled() {
		$enabled = parent::enabled();

		if (empty($this->settings->imgixDomains)) {
			return false;
		}

		return $enabled;
	}

    public function hasSettings() {
        return true;
    }

	public function hasWizard() {
		return true;
	}

	public function wizardLink() {
		return admin_url('admin.php?page=media-cloud-wizard&wizard=imgix');
	}

	public function setup() {
		if(!$this->enabled()) {
			return;
		}

		parent::setup();

		if($this->settings->enabledAlternativeFormats) {
			add_filter('file_is_displayable_image', [$this, "fileIsDisplayableImage"], 0, 2);

			add_filter('upload_mimes', function($mime_types) {
				$mime_types['ai'] = "application/vnd.adobe.illustrator";

				return $mime_types;
			}, 1, 1);

			add_filter('wp_generate_attachment_metadata', [$this, "generateAttachmentMetadata"], 1000, 2);

			add_filter('wp_check_filetype_and_ext', function($file, $filePath, $filename, $mimes) {
				if($file['ext'] == 'psd') {
					$file['type'] = 'image/psd';
				}

				if($file['ext'] == 'ai') {
					$file['type'] = 'application/vnd.adobe.illustrator';
				}

				return $file;
			}, 0, 4);
		}

        add_filter('media_send_to_editor', [$this, 'mediaSendToEditor'], 0, 3);
		add_filter('imgix_build_gif_mpeg4', [$this, 'buildMpeg4'], 0, 3);
		add_filter('imgix_build_gif_jpeg', [$this, 'buildGifJpeg'], 0, 3);

		add_filter('media-cloud/imgix/render-pdf', function() {
			return $this->settings->renderPDF;
		});

		do_action('media-cloud/imgix/setup');

		if ($this->settings->detectFaces) {
			add_filter('media-cloud/storage/after-upload', [$this, 'processImageMeta'], 1000, 2);
        }

        add_filter('media-cloud/imgix/detect-faces', function($detectFaces) {
        	return ($this->enabled() && $this->settings->detectFaces);
        });
	}
	//endregion

    //region URL Generation
	/**
     * Builds the parameters for generating Imgix URLs
     *
	 * @param array $params
	 * @param string $mimetype
	 *
	 * @return array
	 */
	private function buildImgixParams($params, $mimetype = '') {
		$format = null;
		if(!isset($params['fm'])) {
			if(($mimetype == 'image/gif') && $this->settings->enableGifs) {
				$format = 'gif';
			} else if(($mimetype == 'image/png') && !$this->settings->autoFormat) {
				$format = 'png';
			} else if(($mimetype == 'image/svg+xml') && !$this->settings->autoFormat) {
				$format = null;
			} else if(!$this->settings->autoFormat) {
				$format = 'pjpg';
			}
		} else {
			$format = $params['fm'];
		}

		unset($params['fm']);

		$auto = [];
		if(isset($params['auto'])) {
			$auto = explode(',', $params['auto']);
			unset($params['auto']);
		}

		if (isset($params['flip']) && (strpos($params['flip'], ',') > 0)) {
		    $params['flip'] = 'hv';
        }

		if(!$format) {
			if($this->settings->autoCompress && $this->settings->autoFormat) {
				$auto[] = 'compress';
				$auto[] = 'format';
			} else if($this->settings->autoCompress) {
				$auto[] = 'compress';
			} else if($this->settings->autoFormat) {
				$auto[] = 'format';
			}
		} else if($format) {
			$params['fm'] = $format;
			if($this->settings->autoCompress) {
				$auto[] = 'compress';
			}
		} else {
			$params['fm'] = 'pjpg';
			if($this->settings->autoCompress) {
				$auto[] = 'compress';
			}
		}

		if(count($auto) > 0) {
			$params['auto'] = implode(",", $auto);
		}

		unset($params['enhance']);
		unset($params['redeye']);

		if($this->settings->imageQuality) {
			$params['q'] = $this->settings->imageQuality;
		}

		if (isset($this->paramPropsByType['media-chooser'])) {
			foreach($this->paramPropsByType['media-chooser'] as $key => $info) {
				if(isset($params[$key]) && !empty($params[$key])) {
					$media_id = $params[$key];
					unset($params[$key]);
					$markMeta = wp_get_attachment_metadata($media_id);
					if (isset($markMeta['s3'])) {
						$params[$info['imgix-param']] = '/'.$markMeta['s3']['key'];
					} else {
						$params[$info['imgix-param']] = '/'.$markMeta['file'];
					}
				} else {
					unset($params[$key]);
					if(isset($info['dependents'])) {
						foreach($info['dependents'] as $depKey) {
							unset($params[$depKey]);
						}
					}
				}
			}
		}

		if(isset($params['border-width']) && isset($params['border-color'])) {
			$params['border'] = $params['border-width'].','.str_replace('#', '', $params['border-color']);
		}

		unset($params['border-width']);
		unset($params['border-color']);

		if(isset($params['padding-width'])) {
			$params['pad'] = $params['padding-width'];

			if(isset($params['padding-color'])) {
				$params['bg'] = $params['padding-color'];
			}
		}

		unset($params['padding-width']);
		unset($params['padding-color']);

		if (!empty($this->settings->cropMode) && !anyIsSet($params, 'rect', 'fp-x', 'fp-y')) {
			$position = $this->settings->cropPosition;

			if (isset($params['crop'])) {
				$cropParts = explode(',', $params['crop']);
				if (arrayContainsAny($cropParts, ['faces', 'focalpoint', 'edges', 'entropy'])) {
					return $params;
				}

				if (arrayContainsAny($cropParts, ['left', 'top', 'right', 'bottom', 'center'])) {
					$position = $params['crop'];
				}
			}

			$params['crop'] = str_replace('position', $position, $this->settings->cropMode);
		}

		return $params;
	}

	/**
     * Builds an Imgix URL for a dynamically sized image.
     *
	 * @param int $id
	 * @param array $size
	 *
	 * @return array|bool
	 */
	public function buildSizedImage($id, $size) {
		$meta = wp_get_attachment_metadata($id);
		if(!$meta || empty($meta)) {
			return false;
		}

		if(!isset($meta['s3'])) {
			return [];
		}

		if (isset($meta['s3']['mime-type']) && ($meta['s3']['mime-type'] == 'image/svg+xml') && (!$this->settings->renderSVG)) {
			return [];
		}

		$domain = apply_filters('media-cloud/dynamic-images/override-domain', !empty($this->settings->imgixDomains) ? $this->settings->imgixDomains[0] : null);
		$imgix = new UrlBuilder($domain, $this->settings->useHTTPS);
		$key = apply_filters('media-cloud/dynamic-images/override-key', $this->settings->signingKey);
		if(!empty($key)) {
			$imgix->setSignKey($key);
		}

		if (!empty($this->settings->removeQueryVars)) {
			$imgix->setIncludeLibraryParam(false);
		}

		if (isset($size['crop'])) {
			$is_crop = !empty($size['crop']);
		} else {
			$is_crop = ((count($size) >= 3) && ($size[2] == 'crop'));
		}

		if (!$is_crop && $this->shouldCrop) {
		    $this->shouldCrop = false;
		    $is_crop = true;
        }

        if ($is_crop && (($size[0] === 0) || ($size[1] === 0))) {
		    if ($size[0] === 0) {
		        $size[0] = 10000;
            } else {
		        $size[1] = 10000;
            }

		    $is_crop = false;
        }

		if(isset($size['width'])) {
			$size = [
				$size['width'],
				$size['height']
			];
		}

		$mimetype = get_post_mime_type($id);

		if(isset($meta['imgix-params']) && !$this->skipSizeParams) {
			$params = $meta['imgix-params'];
		} else {
			$params = [];
		}

		$params = array_merge($params, [
			'fit' => ($is_crop) ? 'crop' : 'fit',
			'w' => $size[0],
			'h' => $size[1],
			'fm' => 'jpg'
		]);

		$params = $this->buildImgixParams($params, $mimetype);
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

		if (($mimetype === 'application/pdf') && (!empty(arrayPath($meta, 'sizes/full/s3')))) {
			$imageFile = arrayPath($meta, 'sizes/full/s3/key');
		} else {
			$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];
		}

		$doNotUrlEncode = apply_filters('media-cloud/dynamic-images/do-not-url-encode', $this->settings->doNotUrlEncode);
		$result = [
			$imgix->createURL(str_replace(['%2F', '%2540', '%40'], ['/', '@', '@'], $doNotUrlEncode ? $imageFile : urlencode($imageFile)), $params),
			$size[0],
			$size[1]
		];

		return $result;
	}

	/**
	 * Builds an Imgix URL for an MPEG-4 video for an animated GIF source.  This is called with apply_filter('imgix_build_gif_mpeg4').
	 *
	 * @param mixed $value
	 * @param int $postId
	 * @param string $size
	 *
	 * @return array
	 */
	public function buildMpeg4($value, $postId, $size) {
		return $this->buildImage($postId, $size, null, false, ['fmt' => 'mp4']);
	}

	/**
	 * Builds an Imgix URL for a poster jpeg for an animated GIF source.  This is called with apply_filter('imgix_build_gif_jpeg').
	 *
	 * @param mixed $value
	 * @param int $postId
	 * @param string $size
	 *
	 * @return array
	 */
	public function buildGifJpeg($value, $postId, $size) {
		return $this->buildImage($postId, $size, null, false, ['fmt' => 'pjpg']);
	}

	/**
	 * Builds the URL for a srcSet Image
	 *
	 * @param int $post_id
	 * @param array $parentSize
	 * @param array $newSize
	 *
	 * @return array|bool
	 */
	public function buildSrcSetURL($post_id, $parentSize, $newSize) {
		return $this->buildImage($post_id, $parentSize, null, false, null, $newSize);
	}

	/**
	 * Builds an Imgix URL
	 *
	 * @param int $id
	 * @param string|array $size
	 * @param array|null $params
	 * @param bool $skipParams
	 * @param array|null $mergeParams
	 * @param array|null $newSize
     * @param array|null $newMeta
	 *
	 * @return array|bool
	 */
	public function buildImage($id, $size, $params = null, $skipParams = false, $mergeParams = null, $newSize = null, $newMeta=null) {
		if(is_array($size)) {
			return $this->buildSizedImage($id, $size);
		}

		$mimetype = get_post_mime_type($id);

		if (!$this->settings->renderSVG && ($mimetype == 'image/svg+xml')) {
			return false;
		}

//		if (!$this->settings->renderPDF && ($mimetype == 'application/pdf')) {
//			return false;
//		}

		$meta = wp_get_attachment_metadata($id);
		if(!$meta || empty($meta)) {
			if(($this->settings->renderPDF && ($mimetype == 'application/pdf')) || ($this->settings->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
				$meta = get_post_meta($id, 'ilab_s3_info', true);
			}

			if(!$meta || empty($meta)) {
			    if (!empty($newMeta)) {
			        $meta = $newMeta;
                } else {
				    return false;
                }
			}
		}

		if(!isset($meta['s3'])) {
			if(($this->settings->renderPDF && ($mimetype == 'application/pdf')) || ($this->settings->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
				$meta = get_post_meta($id, 'ilab_s3_info', true);
			} else {
				return false;
			}
		}

		$domain = apply_filters('media-cloud/dynamic-images/override-domain', !empty($this->settings->imgixDomains) ? $this->settings->imgixDomains[0] : null);
		$imgix = new UrlBuilder($domain, $this->settings->useHTTPS);
		$key = apply_filters('media-cloud/dynamic-images/override-key', $this->settings->signingKey);
		if(!empty($key)) {
			$imgix->setSignKey($key);
		}

		if (!empty($this->settings->removeQueryVars)) {
			$imgix->setIncludeLibraryParam(false);
		}

		if (($mimetype === 'application/pdf') && (!empty(arrayPath($meta, 'sizes/full/s3')))) {
			if(!isset($meta['width']) || !isset($meta['height'])) {
				$meta['width'] = isset($meta['width']) ? $meta['width'] : arrayPath($meta, 'sizes/full/width');
				$meta['height'] = isset($meta['height']) ? $meta['height'] : arrayPath($meta, 'sizes/full/height');
			}
		}

		if($size == 'full' && !$newSize) {
			if(!isset($meta['width']) || !isset($meta['height'])) {
				return false;
			}

			if(!$params) {
				if(isset($meta['imgix-params']) && !$this->skipSizeParams) {
					$params = $meta['imgix-params'];
				} else {
					$params = [];
				}
			}

			$params = $this->buildImgixParams($params, $mimetype);
			$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

			if(!isset($meta['file'])) {
				return null;
			}

			if (!isset($meta['s3']['key']) && ($meta['s3']['provider'] == 'google')) {
				$path = parse_url($meta['s3']['url'], PHP_URL_PATH);
				$pathParts = explode('/', ltrim($path, '/'));
				array_shift($pathParts);
				$meta['s3']['key'] = $imageFile = ltrim(implode('/', $pathParts), '/');

				update_post_meta($id, '_wp_attachment_metadata', $meta);
			} else {
				if (($mimetype === 'application/pdf') && (!empty(arrayPath($meta, 'sizes/full/s3')))) {
					$imageFile = arrayPath($meta, 'sizes/full/s3/key');
				} else {
					$imageFile = (isset($meta['s3']['key'])) ? $meta['s3']['key'] : $meta['file'];
				}
			}


			$doNotUrlEncode = apply_filters('media-cloud/dynamic-images/do-not-url-encode', $this->settings->doNotUrlEncode);
			$result = [
				$imgix->createURL(str_replace(['%2F', '%2540', '%40'], ['/', '@', '@'], $doNotUrlEncode ? $imageFile : urlencode($imageFile)), ($skipParams) ? [] : $params),
				$meta['width'],
				$meta['height'],
				false
			];

			return $result;
		}

		if($newSize) {
			$sizeInfo = $newSize;
		} else {
			$sizeInfo = ilab_get_image_sizes($size);
		}

		if(!$sizeInfo) {
			return false;
		}

		$metaSize = null;
		if(isset($meta['sizes'][$size])) {
			$metaSize = $meta['sizes'][$size];
		}

		$doCrop = !empty($sizeInfo['crop']) || !empty($metaSize['crop']);

		if(!$params) {
		    $sizeParams = (!empty($sizeInfo['imgix']) && is_array($sizeInfo['imgix'])) ? $sizeInfo['imgix'] : [];
		    $sizeCropParams = (isset($sizeParams['crop'])) ? $sizeParams['crop'] : [];
		    if (!empty($sizeCropParams)) {
                if (is_string($sizeCropParams)) {
                    $sizeCropParams = explode(',', $sizeCropParams);
                    unset($sizeParams['crop']);
                }

                $doCrop = true;
            }

            // get the settings for this image at this size
			if(isset($meta['imgix-size-params'][$size])) {
				$params = array_merge($sizeParams, $meta['imgix-size-params'][$size]);
			}


			if(!$params || (count($params) == 0)) // see if a preset has been globally assigned to a size and use that
			{
				$presets = get_option('ilab-imgix-presets');
				$sizePresets = get_option('ilab-imgix-size-presets');

				if($presets && $sizePresets && isset($sizePresets[$size]) && isset($presets[$sizePresets[$size]])) {
					$params = array_merge($sizeParams, $presets[$sizePresets[$size]]['settings']);
				}
			}

			// still no parameters?  use any that may have been assigned to the full size image
			if((!$params || (count($params) == 0)) && (isset($meta['imgix-params']))) {
				$params = array_merge($sizeParams, $meta['imgix-params']);
			} else if(!$params) // too bad so sad
			{
				$params = $sizeParams;
			}
		}

		if ($doCrop) {
			if (empty($sizeInfo['crop']) && !empty($metaSize['crop'])) {
				$sz = sizeToFitSize($metaSize['crop']['w'],$metaSize['crop']['h'], $sizeInfo['width'] ?: $sizeInfo['height'], $sizeInfo['height'] ?: $sizeInfo['width']);
				$params['w'] = $sz[0];
				$params['h'] = $sz[1];
			} else {
				$params['w'] = $sizeInfo['width'] ?: $sizeInfo['height'];
				$params['h'] = $sizeInfo['height'] ?: $sizeInfo['width'];
			}

			$params['fit'] = 'crop';

			if($metaSize) {
				$metaSize = $meta['sizes'][$size];
				if(isset($metaSize['crop'])) {
					$metaSize['crop']['x'] = round($metaSize['crop']['x']);
					$metaSize['crop']['y'] = round($metaSize['crop']['y']);
					$metaSize['crop']['w'] = round($metaSize['crop']['w']);
					$metaSize['crop']['h'] = round($metaSize['crop']['h']);
					$params['rect'] = implode(',', $metaSize['crop']);
				}
			}

			// we don't want to scale animated gifs AT ALL on the front end
			if(($mimetype == 'image/gif') && (!is_admin())) {
				$imageW = $meta['width'];
				$imageH = $meta['height'];

				$pw = $params['w'];
				$ph = $params['h'];

				if(($pw > $imageW) || ($ph > $imageH)) {
					$newSize = sizeToFitSize($pw, $ph, $imageW, $imageH);
					$params['w'] = $newSize[0];
					$params['h'] = $newSize[1];
				}
			}

			if (isset($params['focalpoint'])) {
				if (!empty($metaSize['crop'])) {
					unset($params['crop']);
					unset($params['fp-x']);
					unset($params['fp-y']);
					unset($params['fp-z']);
					unset($params['focalpoint']);
				} else {
					unset($params['rect']);

					if ($params['focalpoint'] == 'entropy') {
						$params['crop'] = 'entropy';
						unset($params['fp-x']);
						unset($params['fp-y']);
						unset($params['fp-z']);
					} else if ($params['focalpoint'] == 'edges') {
						$params['crop'] = 'edges';
						unset($params['fp-x']);
						unset($params['fp-y']);
						unset($params['fp-z']);
					} else {
						$params['crop'] = 'focalpoint';
						if ($params['focalpoint'] == 'usefaces') {
							unset($params['fp-x']);
							unset($params['fp-y']);

							if (isset($meta['faces'])) {
								$faceindex = arrayPath($params,'faceindex', 0);
								if ((count($meta['faces'])>1) && ($faceindex == 0)) {
									$left = 900000;
									$top = 900000;
									$right = 0;
									$bottom = 0;

									foreach($meta['faces'] as $face) {
										$bb = $face['BoundingBox'];
										$left = min($left, $bb['Left']);
										$top = min($top, $bb['Top']);
										$right = max($right, $bb['Left'] + $bb['Width']);
										$bottom = max($bottom, $bb['Top'] + $bb['Height']);
									}

									$params['fp-x'] = $left + (($right - $left) / 2.0);
									$params['fp-y'] = $top + (($bottom - $top) / 2.0);
								} else {
									$faceindex = min(1,count($meta['faces'])+1);
									$bb = $meta['faces'][$faceindex - 1]['BoundingBox'];

									$params['fp-x'] = $bb['Left'] + ($bb['Width'] / 2.0);
									$params['fp-y'] = $bb['Top'] + ($bb['Height'] / 2.0);
								}

							} else {
								unset($params['crop']);
								unset($params['fp-z']);
							}
						}
					}
				}
            } else {
				unset($params['fp-x']);
				unset($params['fp-y']);
				unset($params['fp-z']);

                $cropParams = [];

                if (!empty($sizeInfo['crop']) && is_array($sizeInfo['crop'])) {
                    list($cropX, $cropY) = $sizeInfo['crop'];
                    if (!empty($cropX) && ($cropX != 'center')) {
                        $cropParams[] = $cropX;
                    }
                    if (!empty($cropY) && ($cropY != 'center')) {
                        $cropParams[] = $cropY;
                    }
                }

                if (!empty($sizeCropParams)) {
                    $cropParams = array_merge($cropParams, $sizeCropParams);
                }

                if (!empty($cropParams)) {
                    $params['crop'] = implode(",", $cropParams);
                }
            }
		} else {
			$mw = !empty($meta['width']) ? $meta['width'] : 10000;
			$mh = !empty($meta['height']) ? $meta['height'] : 10000;

			$w = !empty($sizeInfo['width']) ? $sizeInfo['width'] : 10000;
			$h = !empty($sizeInfo['height']) ? $sizeInfo['height'] : 10000;

			$newSize = sizeToFitSize($mw, $mh, $w, $h);
			$params['w'] = $newSize[0];
			$params['h'] = $newSize[1];
			$params['fit'] = 'scale';

			unset($params['fp-x']);
			unset($params['fp-y']);
			unset($params['fp-z']);
		}

		unset($params['focalpoint']);
		unset($params['faceindex']);

		if($mergeParams && is_array($mergeParams)) {
			$params = array_merge($params, $mergeParams);
		}

		if($size && !is_array($size) && empty($this->settings->removeQueryVars)) {
			$params['wpsize'] = $size;
		}

		$params = $this->buildImgixParams($params, $mimetype);
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

		if (!isset($meta['s3']['key']) && ($meta['s3']['provider'] == 'google')) {
			$path = parse_url($meta['s3']['url'], PHP_URL_PATH);
			$pathParts = explode('/', ltrim($path, '/'));
			array_shift($pathParts);
			$meta['s3']['key'] = $imageFile = ltrim(implode('/', $pathParts), '/');

			update_post_meta($id, '_wp_attachment_metadata', $meta);
		} else {
			if (($mimetype === 'application/pdf') && (!empty(arrayPath($meta, 'sizes/full/s3')))) {
				$imageFile = arrayPath($meta, 'sizes/full/s3/key');
			} else {
				$imageFile = (isset($meta['s3']['key'])) ? $meta['s3']['key'] : $meta['file'];
			}
		}

		$result = [
			$imgix->createURL(str_replace(['%2F', '%2540', '%40'], ['/', '@', '@'], $imageFile), $params),
			$params['w'],
			$params['h'],
			true
		];

		return $result;
	}

	public function urlForStorageMedia($key, $params = []) {
		$domain = apply_filters('media-cloud/dynamic-images/override-domain', !empty($this->settings->imgixDomains) ? $this->settings->imgixDomains[0] : null);
		$imgix = new UrlBuilder($domain, $this->settings->useHTTPS);
		$signingKey = apply_filters('media-cloud/dynamic-images/override-key', $this->settings->signingKey);
		if(!empty($signingKey)) {
			$imgix->setSignKey($signingKey);
		}

		if (!empty($this->settings->removeQueryVars)) {
			$imgix->setIncludeLibraryParam(false);
		}

		$doNotUrlEncode = apply_filters('media-cloud/dynamic-images/do-not-url-encode', $this->settings->doNotUrlEncode);
		return $imgix->createURL(str_replace(['%2F', '%2540', '%40'], ['/', '@', '@'], $doNotUrlEncode ? $key : urlencode($key)), $params);
	}

	public function fixCleanedUrls($good_protocol_url, $original_url, $context) {
		foreach($this->settings->imgixDomains as $domain) {
			if (strpos($good_protocol_url, $domain) !== false) {
				return $original_url;
			}
		}

		return $good_protocol_url;
	}
	//endregion

    //region Face Detection
	/**
	 * Process an image through Rekognition
	 *
	 * @param array $meta
	 * @param int $postID
	 *
	 * @return array
	 */
	public function processImageMeta($meta, $postID) {
		if (!$this->enabled()) {
			return $meta;
		}

        if (apply_filters('media-cloud/vision/detect-faces', false)) {
	        return $meta;
        }

		if (!isset($meta['s3'])) {
			Logger::warning( "Post $postID is  missing 's3' metadata.", $meta, __METHOD__, __LINE__);
			return $meta;
		}

		$url = $this->buildImage($postID, 'full',['fm' => 'json','faces'=>1],false,null,null,$meta);
		if ($url && is_array($url)) {
			$jsonString = ilab_file_get_contents($url[0]);
			if (!empty($jsonString)) {
				$data = json_decode($jsonString, true);

				$pw = arrayPath($data,'PixelWidth',0);
				$ph = arrayPath($data,'PixelHeight',0);

				if (!empty($pw) && !empty($ph)) {
				    $facesData = arrayPath($data,'Faces',[]);
					$faces = [];
				    foreach($facesData as $face) {
					    $faces[] = [
						    'BoundingBox' => [
							    'Left' => floatval($face['bounds']['x']) / floatval($pw),
							    'Top' => floatval($face['bounds']['y']) / floatval($ph),
							    'Width' => floatval($face['bounds']['width']) / floatval($pw),
							    'Height' => floatval($face['bounds']['height']) / floatval($ph),
						    ]
					    ];
                    }

					if (count($faces)>0) {
						$meta['faces'] = $faces;
					}
                }
            }
		}

		return $meta;
	}
    //endregion

    //region WordPress Hooks & Filters
	/**
	 * Filters the attachment data prepared for JavaScript. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L3279)
	 *
	 * @param array $response
	 * @param int|object $attachment
	 * @param array $meta
	 *
	 * @return array
	 */
	function prepareAttachmentForJS($response, $attachment, $meta) {
		if(!$response || empty($response) || !isset($response['sizes'])) {
			return $response;
		}

		if($this->settings->renderPDF && isset($meta['s3'])) {
			if($attachment->post_mime_type == 'application/pdf') {
				$response['type'] = 'image';
				$response['mime'] = 'image/pdf';
				if(isset($meta['width'])) {
					$response['width'] = round($meta['width']);
				}

				if(isset($meta['height'])) {
					$response['height'] = round($meta['height']);
				}

				if(isset($response['width']) && isset($response['height'])) {
					$response['orientation'] = ($response['width'] > $response['height']) ? 'landscape' : 'portrait';
				}
			}
		}

		$generateUrls = !($this->settings->skipGifs && ($attachment->post_mime_type == 'image/gif'));
		if ($generateUrls) {
            foreach($response['sizes'] as $key => $sizeInfo) {
                $res = $this->buildImage($response['id'], $key);
                if(is_array($res)) {
                    $response['sizes'][$key]['url'] = $res[0];
                }
            }
        }

		return $response;
	}

	/**
	 * Filters the attachment's url. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post.php#L5077)
     * @param $url
     * @param $post_id
     * @return mixed|string
     * @throws \MediaCloud\Plugin\Tools\Storage\StorageException
     */
	public function getAttachmentURL($url, $post_id) {
        $mimeType = get_post_mime_type($post_id);

	    if ($this->settings->skipGifs) {
	        if ($mimeType == 'image/gif') {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];

                $gifURL = $storageTool->getAttachmentURL($url, $post_id);
                return $gifURL;
            }
        }

	    if (strpos($mimeType, 'image') !== 0) {
		    if (empty(apply_filters('media-cloud/imgix/allow-non-images', false, $mimeType, $url, $post_id))) {
		    	return $url;
		    }
	    }

        return parent::getAttachmentURL($url, $post_id);
	}

    /**
     * Filters whether to preempt the output of image_downsize().  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L201)
     * @param $fail
     * @param $id
     * @param $size
     * @return array|bool
     * @throws \MediaCloud\Plugin\Tools\Storage\StorageException
     */
	public function imageDownsize($fail, $id, $size) {
        if ($this->settings->skipGifs) {
            $mimeType = get_post_mime_type($id);
            if ($mimeType == 'image/gif') {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];

                $result = $storageTool->forcedImageDownsize($fail, $id, $size);
                return $result;
            }
        }

        if (empty($this->settings->servePrivateImages)) {
			$privacy = StorageToolSettings::privacy('image');
			if (is_string($size)) {
				$privacy = ImageSizePrivacy::privacyForSize($size, $privacy);
			}

			if ($privacy != 'public-read') {
				/** @var StorageTool $storageTool */
				$storageTool = ToolsManager::instance()->tools['storage'];

				$result = $storageTool->forcedImageDownsize($fail, $id, $size);
				return $result;
			}
        }

        return parent::imageDownsize($fail, $id, $size);
	}

	/**
     * Filters the generated attachment meta data. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/image.php#L292)
	 *
     * @param array $metadata
	 * @param int $attachment_id
	 *
	 * @return array
	 */
	public function generateAttachmentMetadata($metadata, $attachment_id) {
		if(!$this->settings->enabledAlternativeFormats) {
			return $metadata;
		}

		$mime = get_post_mime_type($attachment_id);
		if($mime == 'application/vnd.adobe.illustrator') {
			$file = get_attached_file($attachment_id);
			$fileName = pathinfo($file, PATHINFO_BASENAME);
			$fallback_sizes = array(
				'thumbnail',
				'medium',
				'medium_large',
				'large',
			);
			$fallback_sizes = apply_filters('fallback_intermediate_image_sizes', $fallback_sizes, $metadata);
			$additional_sizes = wp_get_additional_image_sizes();

			$metadata = [
				'sizes' => [
					'full' => [
						'file' => $fileName,
						'width' => null,
						'height' => null,
						'mime-type' => 'image/jpeg'
					]
				]
			];

			foreach($fallback_sizes as $sz) {
				$metadata['sizes'][$sz] = [
					'file' => $fileName,
					'width' => get_option("{$sz}_size_w"),
					'height' => get_option("{$sz}_size_h"),
					'mime-type' => 'image/jpeg'
				];
			}

			foreach($additional_sizes as $size => $sizeInfo) {
				if(isset($sizeInfo['crop']) && $sizeInfo['crop']) {
					$metadata['sizes'][$size] = [
						'file' => $fileName,
						'width' => $sizeInfo['width'],
						'height' => $sizeInfo['height'],
						'mime-type' => 'image/jpeg'
					];
				}
			}

			$upload_info = wp_upload_dir();
			$upload_path = $upload_info['basedir'];
			$metadata['file'] = trim(str_replace($upload_path, '', $file), '/');
		}

		return $metadata;
	}

	/**
     * Filters whether the current image is displayable in the browser. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/image.php#L558)
	 *
     * @param bool $result
	 * @param string $path
	 *
	 * @return bool
	 */
	public function fileIsDisplayableImage($result, $path) {
		$mime = wp_get_image_mime($path);
		if(!$mime) {
			$ftype = wp_check_filetype($path);
			if(!empty($ftype) && isset($ftype['type'])) {
				$mime = $ftype['type'];
			}

			$ext = pathinfo($path, PATHINFO_EXTENSION);
			if(('ai' == strtolower($ext)) && ("application/pdf" == $mime)) {
				return true;
			}
		}

		if($mime == 'application/vnd.adobe.illustrator') {
			return true;
		}

		if($mime == 'image/tiff') {
			return true;
		}

		if($mime == 'image/psd') {
			return true;
		}

		return $result;
	}

	/**
     * Filters the HTML markup for a media item sent to the editor. (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/media.php#L739)
	 *
     * @param string $html
	 * @param int $id
	 * @param array $attachment
	 *
	 * @return string
	 */
	public function mediaSendToEditor($html, $id, $attachment) {
		if(!$this->settings->renderPDF) {
			return $html;
		}

		$mime = get_post_mime_type($id);
		if($mime == 'application/pdf') {
			$align = isset($attachment['align']) ? $attachment['align'] : 'none';
			$size = isset($attachment['image-size']) ? $attachment['image-size'] : 'medium';
			$alt = isset($attachment['image_alt']) ? $attachment['image_alt'] : '';

			// No whitespace-only captions.
			$caption = isset($attachment['post_excerpt']) ? $attachment['post_excerpt'] : '';
			if('' === trim($caption)) {
				$caption = '';
			}

			$url = empty($attachment['url']) ? '' : $attachment['url'];
			$rel = (strpos($url, 'attachment_id') || get_attachment_link($id) == $url);

			$title = ''; // We no longer insert title tags into <img> tags, as they are redundant.
			$html = get_image_send_to_editor($id, $caption, $title, $align, $url, $rel, $size, $alt);

		}

		return $html;
	}
	//endregion


    //region Testing

    public function urlForKey($imageKey) {
	    $domain = apply_filters('media-cloud/dynamic-images/override-domain', !empty($this->settings->imgixDomains) ? $this->settings->imgixDomains[0] : null);
	    $imgix = new UrlBuilder($domain, $this->settings->useHTTPS);
	    $key = apply_filters('media-cloud/dynamic-images/override-key', $this->settings->signingKey);
	    if(!empty($key)) {
		    $imgix->setSignKey($key);
	    }

	    if (!empty($this->settings->removeQueryVars)) {
		    $imgix->setIncludeLibraryParam(false);
	    }

	    return $imgix->createURL($imageKey, []);
    }

    //endregion


	//region Wizard
	/**
	 * @param WizardBuilder|null $builder
	 *
	 * @return WizardBuilder|null
	 * @throws \Exception
	 */
	public static function configureWizard($builder = null) {
		if ($builder == null) {
			$builder = new WizardBuilder('imgix', true);
		}

		$builder
			->section('imgix', true)
				->select('Intro', "Let's get started setting up imgix!")
					->group('wizard.imgix.intro', 'select-buttons')
						->option('imgix-doc', 'imgix Documentation', null, null, null, null, 'https://docs.imgix.com/setup', '_blank')
					->endGroup()
				->endStep()
				->form('wizard.imgix.settings', 'imgix Settings', 'Configure Media Cloud with your imgix settings.', [ImgixTool::class, 'processWizardSettings'])
					->hiddenField('nonce', wp_create_nonce('update-imgix-settings'))
					->textField('mcloud-imgix-domains', 'imgix Domain', "Your imgix domain name. For more information, please read the <a href='https://www.imgix.com/docs/tutorials/creating-sources' target='_blank'>imgix documentation</a>", null)
					->passwordField('mcloud-imgix-signing-key', 'Signing Key', "Optional signing key to create secure URLs.  <strong>Recommended</strong>.  For information on setting it up, refer to the <a href='https://www.imgix.com/docs/tutorials/securing-images' target='_blank'>imgix documentation</a>.", null, false)
					->checkboxField('mcloud-imgix-use-https', 'Use HTTPS', 'Use HTTPS for image URLs', true)
				->endStep()
				->testStep('wizard.imgix.test', 'Test Settings', 'Perform tests to insure that imgix is configured correctly.', false)
					->test('Test imgix Configuration', 'Running tests ...', [static::class, 'testImgix'])
				->endStep()
				->select('Complete', 'imgix setup is now complete!')
					->group('wizard.imgix.success', 'select-buttons')
						->option('other-features', 'Explore Other Features', null, null, null, null, 'admin:admin.php?page=media-cloud')
						->option('advanced-imgix-settings', 'Finish & Exit Wizard', null, null, null, null, 'admin:admin.php?page=media-cloud-settings-imgix')
					->endGroup()
				->endStep();

		return $builder;
	}

	public static function processWizardSettings() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update-imgix-settings')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		$domain = arrayPath($_POST, 'mcloud-imgix-domains', null);
		$signingKey = arrayPath($_POST, 'mcloud-imgix-signing-key', null);
		$useHttps = arrayPath($_POST, 'mcloud-imgix-use-https', null);

		if (empty($domain)) {
			wp_send_json(['status' => 'error', 'message' => 'Missing imgix domain.'], 200);
		}

		Environment::UpdateOption('mcloud-imgix-domains', $domain);
		Environment::UpdateOption('mcloud-imgix-signing-key', $signingKey);
		Environment::UpdateOption('mcloud-imgix-use-https', $useHttps);

		wp_send_json([ 'status' => 'ok'], 200);
	}

	public static function testImgix() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media-cloud-wizard-test')) {
			wp_send_json(['status' => 'error', 'message' => 'Nonce is invalid.  Please try refreshing the page and submitting the form again.'], 200);
		}

		Tracker::trackView("System Test - Imgix", "/system-test/imgix");

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		if (!$storageTool->enabled()) {
			wp_send_json([
				'status' => 'error',
				'message' => 'Cloud Storage is not currently enabled.',
				'errors' => []
			]);
		}

		/** @var ImgixTool $imgixTool */
		$imgixTool = ToolsManager::instance()->tools['imgix'];


		$errors = [];

		try {
			$storageTool->client()->upload('_troubleshooter/sample.jpg',ILAB_TOOLS_DIR.'/public/img/test-image.jpg', StorageToolSettings::privacy());
			$imgixURL = $imgixTool->urlForKey('_troubleshooter/sample.jpg');

			$faster = new FasterImage();
			$result = $faster->batch([$imgixURL]);
			$result = $result[$imgixURL];
			$size = $result['size'];

			if (empty($size) || ($size == 'failed')) {
				$errors[] = "Unable to access <a href='$imgixURL'>Imgix sample image</a>.  Possibly wrong signing key or Imgix can't access the master image.";
			} else if (count($size) > 1) {
				list($w, $h) = $size;

				if (($w != 320) && ($h != 320)) {
					$errors[] = "Invalid image size for sample image.  $w x $h (should be 320 x 320)";
				}
			}
		} catch (\Exception $ex) {
			$errors[] = $ex->getMessage();
		}

		if (empty($errors)) {
			Environment::UpdateOption('mcloud-tool-enabled-imgix', true);
			Environment::UpdateOption('mcloud-tool-enabled-crop', true);

			Tracker::trackView("System Test - Imgix - Success", "/system-test/imgix/success");
			wp_send_json([
				'status' => 'success',
				'message' => 'Was able to view sample image on imgix.'
			]);
		} else {
			Environment::UpdateOption('mcloud-tool-enabled-imgix', false);
			Tracker::trackView("System Test - Imgix - Error", "/system-test/imgix/error");
			wp_send_json([
				'status' => 'error',
				'message' => 'There was an error attempting to view an image on imgix.  Please check that your domain or signing key are correct.',
				'errors' => []
			]);
		}
	}

	//endregion
}
