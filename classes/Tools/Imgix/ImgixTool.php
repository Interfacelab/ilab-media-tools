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

namespace ILAB\MediaCloud\Tools\Imgix;

use ILAB\MediaCloud\Tools\DynamicImages\DynamicImagesTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use Imgix\UrlBuilder;
use function ILAB\MediaCloud\Utilities\arrayPath;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}


/**
 * Class ImgixTool
 *
 * Imgix tool.
 */
class ImgixTool extends DynamicImagesTool {
	protected $imgixDomains;
	protected $autoFormat;
	protected $autoCompress;
	protected $enableGifs;
	protected $skipGifs;
	protected $noGifSizes;
	protected $useHTTPS;
	protected $enabledAlternativeFormats;
	protected $renderPDF;
	protected $detectFaces;

    //region Constructor
    public function __construct($toolName, $toolInfo, $toolManager) {
	    parent::__construct($toolName, $toolInfo, $toolManager);

        add_filter('media-cloud/imgix/enabled', function($enabled){
            return $this->enabled();
        });

	    add_filter('media-cloud/imgix/alternative-formats/enabled', function($enabled){
	        return ($this->enabled() && $this->enabledAlternativeFormats);
        });
    }
    //endregion

	//region Tool Overrides
	public function enabled() {
		$enabled = parent::enabled();

		if(!Environment::Option('mcloud-imgix-domains')) {
//			NoticeManager::instance()->displayAdminNotice('error', "To start using Imgix, you will need to <a href='admin.php?page=media-tools-imgix'>set it up</a>.", true, 'disable-ilab-imgix-warning');
			return false;
		}

		return $enabled;
	}

    public function hasSettings() {
        return true;
    }

	public function setup() {
		if(!$this->enabled()) {
			return;
		}

		parent::setup();

		$this->noGifSizes = [];
		$noGifSizes = Environment::Option('mcloud-imgix-no-gif-sizes', null, '');
		$noGifSizesArray = explode("\n", $noGifSizes);
		if(count($noGifSizesArray) <= 1) {
			$noGifSizesArray = explode(',', $noGifSizes);
		}
		foreach($noGifSizesArray as $gs) {
			if(!empty($gs)) {
				$this->noGifSizes[] = trim($gs);
			}
		}

		$this->imgixDomains = [];
		$domains = Environment::Option('mcloud-imgix-domains', null, '');
		$domain_lines = explode("\n", $domains);
		if(count($domain_lines) <= 1) {
			$domain_lines = explode(',', $domains);
		}
		foreach($domain_lines as $d) {
			if(!empty($d)) {
				$this->imgixDomains[] = trim($d);
			}
		}

		$this->useHTTPS = Environment::Option('mcloud-imgix-use-https', null, true);

		$this->signingKey = Environment::Option('mcloud-imgix-signing-key');

		$this->imageQuality = Environment::Option('mcloud-imgix-default-quality');
		$this->autoFormat = Environment::Option('mcloud-imgix-auto-format');
		$this->autoCompress = Environment::Option('mcloud-imgix-auto-compress');
		$this->enableGifs = Environment::Option('mcloud-imgix-enable-gifs');
		$this->skipGifs = Environment::Option('mcloud-imgix-skip-gifs', null, false);
		$this->detectFaces = Environment::Option('mcloud-imgix-detect-faces', null, false);

		$this->enabledAlternativeFormats = Environment::Option('mcloud-imgix-enable-alt-formats');
		$this->renderPDF = Environment::Option('mcloud-imgix-render-pdf-files');
        $this->keepThumbnails = Environment::Option('mcloud-imgix-generate-thumbnails', null, true);

		if($this->enabledAlternativeFormats) {
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
			return $this->renderPDF;
		});

		do_action_deprecated('ilab_imgix_setup', [], '3.0.0', 'media-cloud/imgix/setup');
		do_action('media-cloud/imgix/setup');

		if ($this->detectFaces) {
			add_filter('media-cloud/storage/after-upload', [$this, 'processImageMeta'], 1000, 2);
        }

        add_filter('media-cloud/imgix/detect-faces', function($detectFaces) {
        	return ($this->enabled() && $this->detectFaces);
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
			if(($mimetype == 'image/gif') && $this->enableGifs) {
				$format = 'gif';
			} else if(($mimetype == 'image/png') && !$this->autoFormat) {
				$format = 'png';
			} else if(!$this->autoFormat) {
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
			if($this->autoCompress && $this->autoFormat) {
				$auto[] = 'compress';
				$auto[] = 'format';
			} else if($this->autoCompress) {
				$auto[] = 'compress';
			} else if($this->autoFormat) {
				$auto[] = 'format';
			}
		} else if($format) {
			$params['fm'] = $format;
			if($this->autoCompress) {
				$auto[] = 'compress';
			}
		} else {
			$params['fm'] = 'pjpg';
			if($this->autoCompress) {
				$auto[] = 'compress';
			}
		}

		if(count($auto) > 0) {
			$params['auto'] = implode(",", $auto);
		}

		unset($params['enhance']);
		unset($params['redeye']);

		if($this->imageQuality) {
			$params['q'] = $this->imageQuality;
		}

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

		$imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

		if($this->signingKey) {
			$imgix->setSignKey($this->signingKey);
		}


		$is_crop = ((count($size) >= 3) && ($size[2] == 'crop'));
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

		$params = [
			'fit' => ($is_crop) ? 'crop' : 'fit',
			'w' => $size[0],
			'h' => $size[1],
			'fm' => 'jpg'
		];

		$params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

		$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

		$result = [
			$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), $params),
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

        if (!$this->renderPDF && ($mimetype == 'application/pdf')) {
            return false;
        }

		$meta = wp_get_attachment_metadata($id);
		if(!$meta || empty($meta)) {
			if(($this->renderPDF && ($mimetype == 'application/pdf')) || ($this->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
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
			if(($this->renderPDF && ($mimetype == 'application/pdf')) || ($this->enabledAlternativeFormats && ($mimetype == 'application/vnd.adobe.illustrator'))) {
				$meta = get_post_meta($id, 'ilab_s3_info', true);
			} else {
				return false;
			}
		}

		$imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

		if($this->signingKey) {
			$imgix->setSignKey($this->signingKey);
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
			$params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
			$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

			if(!isset($meta['file'])) {
				return null;
			}

			$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

			$result = [
				$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), ($skipParams) ? [] : $params),
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

		if($size && !is_array($size)) {
			$params['wpsize'] = $size;
		}

		$params = $this->buildImgixParams($params, $mimetype);
		$params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

		$imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

		$result = [
			$imgix->createURL(str_replace('%2F', '/', urlencode($imageFile)), $params),
			$params['w'],
			$params['h'],
			true
		];

		return $result;
	}

	public function urlForStorageMedia($key) {
		$imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

		if($this->signingKey) {
			$imgix->setSignKey($this->signingKey);
		}

		return $imgix->createURL(str_replace('%2F', '/', urlencode($key)), []);
	}

	public function fixCleanedUrls($good_protocol_url, $original_url, $context) {
		foreach($this->imgixDomains as $domain) {
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
			Logger::warning( "Post $postID is  missing 's3' metadata.", $meta);
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

		if($this->renderPDF && isset($meta['s3'])) {
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

		$generateUrls = !($this->skipGifs && ($attachment->post_mime_type == 'image/gif'));
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
     * @throws \ILAB\MediaCloud\Storage\StorageException
     */
	public function getAttachmentURL($url, $post_id) {
	    if ($this->skipGifs) {
	        $mimeType = get_post_mime_type($post_id);
	        if ($mimeType == 'image/gif') {
                /** @var StorageTool $storageTool */
                $storageTool = ToolsManager::instance()->tools['storage'];

                $gifURL = $storageTool->getAttachmentURL($url, $post_id);
                return $gifURL;
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
     * @throws \ILAB\MediaCloud\Storage\StorageException
     */
	public function imageDownsize($fail, $id, $size) {
        if ($this->skipGifs) {
            $mimeType = get_post_mime_type($id);
            if ($mimeType == 'image/gif') {
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
		if(!$this->enabledAlternativeFormats) {
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
		if(!$this->renderPDF) {
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

    public function urlForKey($key) {
        $imgix = new UrlBuilder($this->imgixDomains, $this->useHTTPS);

        if($this->signingKey) {
            $imgix->setSignKey($this->signingKey);
        }

        return $imgix->createURL($key, []);
    }

    //endregion
}
