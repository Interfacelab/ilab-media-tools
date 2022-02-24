<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tools\Storage;

use MediaCloud\Plugin\Tasks\TaskReporter;
use MediaCloud\Plugin\Tools\Debugging\DebuggingToolSettings;
use MediaCloud\Plugin\Tools\Storage\Driver\S3\S3StorageSettings;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\anyEmpty;
use function MediaCloud\Plugin\Utilities\arrayPath;

if (!defined('ABSPATH')) { header('Location: /'); die; }

class StorageContentHooks {
	/** @var array */
	private static $customizedMedia = [];

	/** @var null|array */
	protected $allSizes = null;

	/** @var StorageTool  */
	private $tool;

	/** @var StorageToolSettings  */
	private $settings;

	/** @var DebuggingToolSettings */
	private $debugSettings;

	/** @var TaskReporter[] */
	private $reporters = [];

	public function __construct(StorageTool $tool) {
		$this->tool = $tool;
		$this->settings = StorageToolSettings::instance();
		$this->debugSettings = DebuggingToolSettings::instance();

		if ($this->settings->filterContent) {
			// gutenberg filters
			add_filter('render_block', [$this, 'filterBlocks'], PHP_INT_MAX - 1, 2);
			add_filter('the_content', [$this, 'fixGutenbergFigures'], PHP_INT_MAX - 2, 1);
			add_filter('the_content', [$this, 'filterGutenbergContent'], PHP_INT_MAX - 1, 1);

			// content filters
			add_filter('content_save_pre', [$this, 'filterContent'], PHP_INT_MAX - 1, 1);
			add_filter('excerpt_save_pre', [$this, 'filterContent'], PHP_INT_MAX - 1, 1);
			add_filter('the_excerpt', [$this, 'filterContent'], PHP_INT_MAX - 1, 1);
			add_filter('rss_enclosure', [$this, 'filterContent'], PHP_INT_MAX - 1, 1);
			add_filter('the_content', [$this, 'filterContent'], PHP_INT_MAX - 1, 1);
			add_filter('the_editor_content', [$this, 'filterContent'], PHP_INT_MAX - 1, 2);
			add_filter('wp_video_shortcode', [$this, 'filterVideoShortcode'], PHP_INT_MAX, 5);
			add_filter('wp_audio_shortcode', [$this, 'filterAudioShortcode'], PHP_INT_MAX, 5);

			add_filter('shortcode_atts_video', function($out, $pairs, $atts, $shortcode) {
				$default_types = wp_get_video_extensions();
				foreach($default_types as $type) {
					if (!empty($out[$type])) {
						if (strpos($out[$type], '?') !== false) {
							$out[$type]  = preg_replace('/(\?.*)/', '', $out[$type]);
						}
					}
				}

				return $out;
			}, PHP_INT_MAX, 4);

			add_filter('shortcode_atts_audio', function($out, $pairs, $atts, $shortcode) {
				$default_types = wp_get_audio_extensions();
				foreach($default_types as $type) {
					if (!empty($out[$type])) {
						if (strpos($out[$type], '?') !== false) {
							$out[$type]  = preg_replace('/(\?.*)/', '', $out[$type]);
						}
					}
				}

				return $out;
			}, PHP_INT_MAX, 4);
		}

		// srcset
		add_filter('wp_calculate_image_srcset', [$this, 'calculateSrcSet'], 10000, 5);

		// misc
		add_filter('image_size_names_choose', function($sizes) {
			if ($this->allSizes == null) {
				$this->allSizes = ilab_get_image_sizes();
			}

			foreach($this->allSizes as $sizeKey => $size) {
				if (!isset($sizes[$sizeKey])) {
					$sizes[$sizeKey] = ucwords(preg_replace("/[-_]/", " ", $sizeKey));
				}
			}

			return $sizes;
		});


		add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id, $mode) {
			static::$customizedMedia[$attachment_id] = $metadata;
			return $metadata;
		}, 0, 3);

		add_action('customize_save_after', function() {
			foreach(static::$customizedMedia as $attachmentId => $metadata) {
				StorageUtilities::instance()->fixMetadata($attachmentId, arrayPath($metadata, 'sizes', []));
			}
		}, PHP_INT_MAX);
	}

	//region Gutenberg Filtering

	private function processFileBlock($id, $block_content) {
		if(preg_match_all('/<a\s+([^>]*)>/m', $block_content, $anchors, PREG_SET_ORDER)) {
			foreach($anchors as $anchor) {
				if(preg_match('/class\s*=\s*"([^"]*)\"/', $anchor[0], $class)) {
					$newAnchor = str_replace($class[1], "{$class[1]} mcloud-attachment-{$id}", $anchor[0]);
				} else {
					$newAnchor = str_replace(">", " class=\"mcloud-attachment-{$id}\">", $anchor[0]);
				}

				$block_content = str_replace($anchor[0], $newAnchor, $block_content);
			}
		}

		return $block_content;
	}

	private function processAudioBlock($id, $block_content) {
		if(preg_match_all('/<audio\s+([^>]*)>/m', $block_content, $audioTags, PREG_SET_ORDER)) {
			foreach($audioTags as $audioTag) {
				if(preg_match('/class\s*=\s*"([^"]*)\"/', $audioTag[0], $class)) {
					$newAudioTag = str_replace($class[1], "{$class[1]} mcloud-attachment-{$id}", $audioTag[0]);
				} else {
					$newAudioTag = str_replace(">", " class=\"mcloud-attachment-{$id}\">", $audioTag[0]);
				}

				if (preg_match('/src\s*=\s*"(.*)\"/', $audioTag[0], $source)) {
					$newUrl = wp_get_attachment_url($id);
					$newAudioTag = str_replace($source[1], $newUrl, $newAudioTag);
				}


				$block_content = str_replace($audioTag[0], $newAudioTag, $block_content);
			}
		}

		return $block_content;
	}

	private function processVideoBlock($id, $block_content) {
		if(preg_match_all('/<video\s+([^>]*)>/m', $block_content, $videoTags, PREG_SET_ORDER)) {
			foreach($videoTags as $videoTag) {
				if(preg_match('/class\s*=\s*"([^"]*)\"/', $videoTag[0], $class)) {
					$newVideoTag = str_replace($class[1], "{$class[1]} mcloud-attachment-{$id}", $videoTag[0]);
				} else {
					$newVideoTag = str_replace(">", " class=\"mcloud-attachment-{$id}\">", $videoTag[0]);
				}

				if (preg_match('/src\s*=\s*"(.*)\"/', $videoTag[0], $source)) {
					$newUrl = wp_get_attachment_url($id);
					$newVideoTag = str_replace($source[1], $newUrl, $newVideoTag);
				}


				$block_content = str_replace($videoTag[0], $newVideoTag, $block_content);
			}
		}

		return $block_content;
	}

	private function processCoverBlock($id, $block_content) {
		if(preg_match_all('/class\s*=\s*"([^"]*)/m', $block_content, $classes, PREG_SET_ORDER)) {
			foreach($classes as $class) {
				if (strpos($class[1], 'inner_container') !== false) {
					continue;
				}

				if (strpos($class[1], 'wp-block-cover') === false) {
					continue;
				}

				$block_content = str_replace($class[1], "{$class[1]} mcloud-attachment-{$id}", $block_content);
			}
		}

		return $block_content;
	}

	private function processGallery($linkType, $block_content) {
		if (preg_match_all('/<a\s+(?:[^>]+)>/m', $block_content, $anchors)) {
			foreach($anchors[0] as $anchor) {
				if (strpos('class=', $anchor) === false) {
					$newAnchor = str_replace('<a ', "<a class=\"{$linkType}-link\" ", $anchor);
				} else {
					$newAnchor = str_replace('class=\"', "class=\"{$linkType}-link ", $anchor);
				}

				$block_content = str_replace($anchor, $newAnchor, $block_content);
			}
		}

		return $block_content;
	}

	/**
	 * Filters the File block to include the goddamn attachment ID
	 *
	 * @param $block_content
	 * @param $block
	 *
	 * @return mixed
	 */
	function filterBlocks($block_content, $block) {
		if (isset($block['blockName'])) {
			$id = arrayPath($block, 'attrs/id');
			if(!empty($id)) {
				if ($block['blockName'] === 'core/file') {
					$block_content = $this->processFileBlock($id, $block_content);
				} else if ($block['blockName'] === 'core/audio') {
					$block_content = $this->processAudioBlock($id, $block_content);
				} else if ($block['blockName'] === 'core/video') {
					$block_content = $this->processVideoBlock($id, $block_content);
				} else if ($block['blockName'] === 'core/cover') {
					$block_content = $this->processCoverBlock($id, $block_content);
				}
			} else {
				if ($block['blockName'] === 'core/gallery') {
					$linkTo = arrayPath($block, 'attrs/linkTo');
					if (!empty($linkTo)) {
						$block_content = $this->processGallery($linkTo, $block_content);
					}
				}
			}
		}

		return $block_content;
	}

	/**
	 * Fixes Gutenberg's image block, why put the size on the f*cking <figure> and not the <img>?
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function fixGutenbergFigures($content) {
		if (!apply_filters('media-cloud/storage/fix-gutenberg-image-blocks', true)) {
			return $content;
		}

		if (preg_match_all('/(<figure(.*)(?=<\/figure>))/m', $content, $figures)) {
			Logger::info("Found ".count($figures[0])." gutenberg figures.", [], __METHOD__, __LINE__);

			foreach($figures[0] as $figureMatch) {
				if (preg_match('/<figure(?:.*)class\s*=\s*(?:.*)wp-block-image(?:.*)size-([aA-zZ0-9-_.]+)/', $figureMatch, $sizeMatches)) {
					$size = $sizeMatches[1];
					if (preg_match('/class\s*=\s*(?:.*)wp-image-([0-9]+)/m', $figureMatch, $imageIdMatches)) {
						$imageId = $imageIdMatches[1];
						if (preg_match('/<img\s+([^>]+)>/m', $figureMatch, $imageTagMatch)) {
							if (preg_match('/\s+src=[\'"]([^\'"]+)[\'"]+/', $imageTagMatch[0], $srcs)) {
								$newUrl = wp_get_attachment_image_src($imageId, $size);
								if (!empty($newUrl)) {
									$newImage = str_replace($srcs[0], " src=\"{$newUrl[0]}\"", $imageTagMatch[0]);
									$newFigure = str_replace($imageTagMatch[0], $newImage, $figureMatch);
									$newFigure = str_replace("wp-image-{$imageId}", "wp-image-{$imageId}", $newFigure);
									$content = str_replace($figureMatch, $newFigure, $content);

									Logger::info("Replaced URL for gutenberg figure using post id $imageId and size $size", [], __METHOD__, __LINE__);
									$this->addToReport($imageId, 'Gutenberg Figure', $srcs[0], $newUrl[0]);
								} else {
									Logger::info("Replacement URL for gutenberg figure using post id $imageId and size $size was null", [], __METHOD__, __LINE__);
									$this->addToReport($imageId, 'Gutenberg Figure', $srcs[0], null, 'Attachment image src is null');
								}
							} else {
								Logger::info("Image tag missing src attribute: {$imageTagMatch[0]}", [], __METHOD__, __LINE__);
							}
						} else {
							Logger::warning("Figure missing img tag: $figureMatch", [], __METHOD__, __LINE__);
						}
					} else {
						Logger::warning("Figure missing wp-image class: $figureMatch", [], __METHOD__, __LINE__);
					}
				} else {
					Logger::warning("Figure missing wp-block-image or size class: $figureMatch", [], __METHOD__, __LINE__);
				}
			}
		}

		return $content;
	}

	/**
	 * Filter the content for the blocks we've already processed
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filterGutenbergContent($content) {
		if (!apply_filters('media-cloud/storage/can-filter-content', true)) {
			return $content;
		}

		//Filter Anchors
		if (preg_match_all( '/<a\s+[^>]+>/m', $content, $anchors) ) {
			foreach($anchors[0] as $anchor) {
				if (preg_match('/mcloud-attachment-([0-9]+)/', $anchor, $attachmentId)) {
					$id = $attachmentId[1];
					if (preg_match('/href\s*=\s*"([^"]+)"/', $anchor, $hrefs)) {
						$newUrl = wp_get_attachment_url($id);
						if ($newUrl !== $hrefs[1]) {
							$content = str_replace($hrefs[1], $newUrl, $content);
							$this->addToReport($id, 'Gutenberg Image Anchor', $hrefs[1], $newUrl);
						} else {
							$this->addToReport($id, 'Gutenberg Image Anchor', $hrefs[1], $newUrl, 'Anchor URL is the same.');
						}
					}
				}
			}
		}

		//Filter Audio or Video Tags
		if (preg_match_all( '/<(?:audio|video)\s+[^>]+>/m', $content, $audioTags) ) {
			foreach($audioTags[0] as $audioTag) {
				if (preg_match('/mcloud-attachment-([0-9]+)/', $audioTag, $attachmentId)) {
					$id = $attachmentId[1];
					if (preg_match('/src\s*=\s*"([^"]+)"/', $audioTag, $srcs)) {
						$newUrl = wp_get_attachment_url($id);
						if ($newUrl !== $srcs[1]) {
							$content = str_replace($srcs[1], $newUrl, $content);
							$this->addToReport($id, 'Gutenberg Audio|Video', $srcs[1], $newUrl);
						} else {
							$this->addToReport($id, 'Gutenberg Audio|Video', $srcs[1], $newUrl, 'URL is the same.');
						}
					}
				}
			}
		}

		// Filter Cover Images
		if (preg_match_all('/<div\s+(?:[^>]+)wp-block-cover(?:[^>]+)>/m', $content, $coverImages)) {
			foreach($coverImages[0] as $coverImage) {
				if (strpos($coverImage, 'background-image') === false) {
					continue;
				}

				if (preg_match('/mcloud-attachment-([0-9]+)/', $coverImage, $attachmentId)) {
					$id = $attachmentId[1];
					if (preg_match('/background-image:url\(([^)]+)\)/', $coverImage, $backgroundUrl)) {
						$newUrl = wp_get_attachment_url($id);
						if ($backgroundUrl[1] === $newUrl) {
							$this->addToReport($id, 'Gutenberg Cover Image', $backgroundUrl[1], $newUrl, 'URL is the same.');

							continue;
						}

						$newCoverImage = str_replace($backgroundUrl[1], $newUrl, $coverImage);
						$content = str_replace($coverImage, $newCoverImage, $content);

						$this->addToReport($id, 'Gutenberg Cover Image', $backgroundUrl[1], $newUrl);
					}
				}
			}
		}

		//Fix Galleries
		$galleryAnchors = [];
		$galleryImages = [];
		preg_match_all('/<li\s+(?:[^>]*)blocks-gallery-item(?:[^>]+)>\s*<figure\s*(?:[^>]*)>\s*(<img[^>]+>)\s*<\/figure>\s*<\/li>/m', $content, $galleryElements);
		if ((count($galleryElements) === 2) && !empty($galleryElements[1])) {
			$galleryImages = $galleryElements[1];
		} else {
			preg_match_all('/<li\s+(?:[^>]*)blocks-gallery-item(?:[^>]+)>\s*<figure\s*(?:[^>]*)>\s*(<a[^>]+>)\s*(<img[^>]+>)\s*<\/a>\s*<\/figure>\s*<\/li>/m', $content, $galleryElements, PREG_SET_ORDER);
			if (!empty($galleryElements)) {
				foreach($galleryElements as $galleryElement) {
					$galleryAnchors[] = $galleryElement[1];
					$galleryImages[] = $galleryElement[2];
				}
			}
		}

		if (!empty($galleryImages) || !empty($galleryAnchors)) {
			$attachmentIds = [];

			foreach($galleryImages as $galleryImage) {
				if (preg_match('/data-id\s*=\s*[\'"]+([0-9]+)/', $galleryImage, $attachmentId)) {
					$id = $attachmentId[1];
					$attachmentIds[] = $id;

					if (preg_match('/data-full-url\s*=\s*["\']([^\'"]+)/m', $galleryImage, $fullUrl)) {
						$newUrl = wp_get_attachment_image_src($id, 'full');
						if (!empty($newUrl) && ($fullUrl[1] !== $newUrl[0])) {
							$newGalleryImage = str_replace($fullUrl[0], "data-full-url=\"{$newUrl[0]}\"", $galleryImage);

							$newUrl = null;

							if (preg_match('/\s+src\s*=\s*["\']([^\'"]+)/m', $newGalleryImage, $srcs)) {
								if (preg_match('/wpsize\s*=\s*([^\s&]+)/m', $srcs[0], $sizeMatches)) {
									$size = $sizeMatches[1];
									$newUrl = wp_get_attachment_image_src($id, $size);
								} else if (preg_match('/([0-9]+)x([0-9]+)\.(?:jpeg|jpg|png|webp|gif)/m', $srcs[0], $sizeMatches)) {
									$width = intval($sizeMatches[1]);
									$height = intval($sizeMatches[2]);

									if (!has_image_size("_gutenberg_{$width}_{$height}_cropped")) {
										add_image_size("_gutenberg_{$width}_{$height}_cropped", $width, $height, true);
									}

									if (!has_image_size("_gutenberg_{$width}_{$height}_fit")) {
										add_image_size("_gutenberg_{$width}_{$height}_fit", $width, $height, false);
									}

									$sizer = ilab_find_nearest_size($id, $width, $height);
									if (empty($sizer)) {
										$sized = image_get_intermediate_size($id, [$width, $height]);
										if (!empty($sized)) {
											$newUrl = [$sized['url']];
										}
									} else {
										$newUrl = wp_get_attachment_image_src($id, $sizer);
									}
								}

								if (empty($newUrl)) {
									$newUrl = wp_get_attachment_image_src($id, 'large');
								}

								if (!empty($newUrl) && (str_replace("&amp;", "&", $srcs[1]) !== $newUrl[0])) {
									$newGalleryImage = str_replace($srcs[0], " src=\"{$newUrl[0]}\"", $newGalleryImage);
									$newGalleryImage = str_replace('class="', "class=\"mcloud-attachment-{$id} ", $newGalleryImage);
									$content = str_replace($galleryImage, $newGalleryImage, $content);

									$this->addToReport($id, 'Gutenberg Gallery Image', $srcs[1], $newUrl[0]);
								} else if (!empty($newUrl)) {
									$this->addToReport($id, 'Gutenberg Gallery Image', $srcs[1], $newUrl[0], "Gallery image URL is the same.");
								} else {
									$this->addToReport($id, 'Gutenberg Gallery Image', $srcs[1], null, "New URL is empty.");
								}
							}
						}
					}
				}
			}

			$anchorIndex = 0;
			foreach($galleryAnchors as $galleryAnchor) {
				if (strpos($galleryAnchor, 'attachment-link') !== false) {
					$anchorIndex++;
					continue;
				}

				if (strpos($galleryAnchor, 'media-link') !== false) {
					if (preg_match('/\s+href\s*=\s*["\']([^\'"]+)/m', $galleryAnchor, $srcs)) {
						if ($anchorIndex < count($attachmentIds)) {
							$id = $attachmentIds[$anchorIndex];

							$newUrl = wp_get_attachment_image_src($id, 'full');
							if (!empty($newUrl) && ($srcs[1] !== $newUrl[0])) {
								$newGalleryAnchor = str_replace($srcs[0], " href=\"{$newUrl[0]}\"", $galleryAnchor);
								$content = str_replace($galleryAnchor, $newGalleryAnchor, $content);
							}
						}
					}
				}

				$anchorIndex++;
			}
		}


		return $content;
	}

	//endregion

	//region Reporter

	/**
	 * @return TaskReporter|null
	 */
	private function getReporter(): ?TaskReporter {
		if (empty($this->debugSettings->debugContentFiltering)) {
			return null;
		}

		$reportId = sanitize_title($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		if (!isset($this->reporters[$reportId])) {
			$reporter = new TaskReporter($reportId, [
				'Post ID', 'Type', 'Found URL', 'Mapped URL', 'Notes'
			], true);
			$reporter->open();

			$this->reporters[$reportId] = $reporter;
		} else {
			$reporter = $this->reporters[$reportId];
		}

		return $reporter;
	}

	private function addToReport($postId = null, $type = null, $oldUrl = null, $newUrl = null, $notes = null) {
		if (empty($this->debugSettings->debugContentFiltering)) {
			return;
		}

		$reporter = $this->getReporter();
		if (!empty($reporter)) {
			$reporter->add([$postId, $type, $oldUrl, $newUrl, $notes]);
		}
	}

	//endregion

	//region Content Filtering

	/**
	 * Filter the content to replace CDN
	 *
	 * @param $content
	 * @param string $context
	 *
	 * @return mixed
	 * @throws StorageException
	 */
	public function filterContent($content, $context = 'post') {
		$startTime = microtime(true);

		if (!apply_filters('media-cloud/storage/can-filter-content', true)) {
			return $content;
		}

		$originalContent = $content;

		if ($context !== 'post') {
			$content = str_replace('&lt;', '<', $content);
			$content = str_replace('&gt;', '>', $content);
		}

//	    if (defined('MEDIACLOUD_DEV_MODE')) {
//		    $content = preg_replace('/wp-image-[0-9]+/', '', $content);
//	    }

		if (!preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
			Logger::info("No image tags found", [], __METHOD__, __LINE__);
			return $originalContent;
		} else {
			Logger::info("Found ".count($matches[0])." image tags.", [], __METHOD__, __LINE__);
		}

		$uploadDir = wp_get_upload_dir();

		$replacements = [];
		$resizedReplacements = [];
		$replacedIds = [];

		$srcRegex = "#src=['\"]+([^'\"]+)['\"]+#m";
		foreach($matches[0] as $image) {
			$imageFound = false;

			if (!preg_match($srcRegex, $image, $srcMatches) || (strpos($image, 'mcloud-attachment-') !== false)) {
				Logger::warning("Image tag has no src attribute or is missing mcloud-attachment class: ".$image, [], __METHOD__, __LINE__);
				continue;
			}

			$src = $srcMatches[1];

			// parse img tags with classes because these usually indicate the wordpress size
			if (preg_match('/class\s*=\s*(?:[\"\']{1})([^\"\']+)(?:[\"\']{1})/m', $image, $matches)) {
				$classes = explode(' ', $matches[1]);

				$size = null;
				$id = null;

				foreach($classes as $class) {
					if (strpos($class, 'wp-image-') === 0) {
						$parts = explode('-', $class);
						$id = array_pop($parts);
					} else if (strpos($class, 'size-') === 0) {
						$size = str_replace('size-', '', $class);
					}
				}

				if (!empty($id) && empty($size)) {
					Logger::warning("Found ID '$id' but no size for image tag: ".$image, [], __METHOD__, __LINE__);

					if (preg_match('/sizes=[\'"]+\(max-(width|height)\:\s*([0-9]+)px/m', $image, $sizeMatches)) {
						$which = $sizeMatches[1];
						$px = $sizeMatches[2];

						$meta = wp_get_attachment_metadata($id);
						if (!empty($meta['sizes'])) {
							foreach($meta['sizes'] as $sizeKey => $sizeData) {
								if ($sizeData[$which] == $px) {
									$size = $sizeKey;
									break;
								}
							}
						}
					}

					if (empty($size)) {
						if (preg_match('/wpsize=([aA-zZ0-9-_]*)/m', $src, $wpSizeMatches)) {
							$size = $wpSizeMatches[1];
						} else {
							if (preg_match('/(([0-9]+)x([0-9]+)\.(?:jpg|jpeg|gif|png))/', $src, $dimensionMatches)) {
								$width = $dimensionMatches[2];
								$height = $dimensionMatches[3];
								$size = ilab_find_nearest_size($id, $width, $height);

								if (empty($size)) {
									$size = 'full';
									Logger::warning("Could not find size for image tag, using full: ".$image, [], __METHOD__, __LINE__);
								}
							} else {
								$size = 'full';
								Logger::warning("Could not find size for image tag, using full: ".$image, [], __METHOD__, __LINE__);
							}
						}
					}
				} else if (!empty($id) && !empty($size)) {
					Logger::info("Found post id {$id} and size {$size} for image tag: ".$image, [], __METHOD__, __LINE__);
				}

				if (!empty($id) && is_numeric($id)) {
					$imageFound = true;
					$replacedIds[$id] = true;
					$replacements["$id,$size"] = [
						'image' => $image,
						'src' => $src,
						'size' => $size
					];
				}
			} else {
				Logger::warning("Image tag has no class attribute: ".$image, [], __METHOD__, __LINE__);
			}

			if (!$imageFound && !empty($this->settings->replaceAllImageUrls)) {
				$escapedBase = str_replace('/', '\/', $uploadDir['baseurl']);
				$escapedBase = str_replace('.', '\.', $escapedBase);
				$imageRegex = "#(data-src|src)\s*=\s*[\'\"]+({$escapedBase}[^\'\"]*(jpg|png|gif))[\'\"]+#";
				if (preg_match($imageRegex, $image, $matches)) {
					$matchedUrl = $matches[2];

					$textSize = null;
					$cleanedUrl = null;
					$size = 'full';

					if (preg_match('/(-[0-9x]+)\.(?:jpg|gif|png)/m', $matchedUrl, $sizeMatches)) {
						$cleanedUrl = str_replace($sizeMatches[1], '', $matchedUrl);
						$id = attachment_url_to_postid($cleanedUrl);
						$textSize = trim($sizeMatches[1], '-');
						$size = explode('x', $textSize);
					} else {
						$id = attachment_url_to_postid($matchedUrl);
					}


					if (!empty($id)) {
						Logger::info("Found post id {$id} for image tag using brute force attachment_url_to_postid(): ".$image, [], __METHOD__, __LINE__);

						$replacedIds[$id] = true;
						if (!empty($textSize)) {
							$resizedReplacements[$id.'-'.$textSize] = [
								'id' => $id,
								'image' => $image,
								'src' => $matchedUrl,
								'size' => $size
							];
						} else {
							$replacements["$id,$size"] = [
								'image' => $image,
								'src' => $matchedUrl,
								'size' => $size
							];
						}
					} else {
						Logger::warning("Unable to map URL to post ID using attachment_url_to_postid(): ".$image, [], __METHOD__, __LINE__);
						$this->addToReport(null, 'Image', $matchedUrl, null, 'Unable to map URL to post ID.');
					}
				} else {
					Logger::warning("Unable to map URL to post ID, no regex match $imageRegex: ".$image, [], __METHOD__, __LINE__);
				}
			} else if (!$imageFound) {
				Logger::warning("Unable to map URL to post ID: ".$image, [], __METHOD__, __LINE__);
				$this->addToReport(null, 'Image', $src, null, 'Unable to map URL to post ID.');
			}
		}

		foreach($replacements as $idSize => $data) {
			$idSizeParts = explode(',', $idSize);
			$content = $this->replaceImageInContent($idSizeParts[0], $data, $content);
		}

		foreach($resizedReplacements as $id => $data) {
			$content = $this->replaceImageInContent($data['id'], $data, $content);
		}

		if ($this->settings->replaceAnchorHrefs) {
			if (count($replacedIds) > 0) {
				$replacementHrefs = [];

				add_filter('media-cloud/storage/override-url', '__return_false');
				add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
				foreach($replacedIds as $id => $cool) {
					$src = wp_get_attachment_image_src($id, 'full');

					if(empty($src)) {
						continue;
					}

					$replacementHrefs[$id] = ['original' => $src[0]];
				}
				remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true');
				remove_filter('media-cloud/storage/override-url', '__return_false');

				foreach($replacedIds as $id => $cool) {
					$src = wp_get_attachment_image_src($id, 'full');

					if(empty($src) || !isset($replacementHrefs[$id])) {
						continue;
					}

					$replacementHrefs[$id]['replacement'] = $src[0];
				}

				foreach($replacementHrefs as $id => $replacement) {
					$og = str_replace('/', '\/', $replacement['original']);
					$og = str_replace('.','\.', $og);
					$re = '/href\s*=\s*(?:\'|")\s*'.$og.'\s*(?:\'|")/m';
					$content = preg_replace($re, "href=\"{$replacement['replacement']}\"", $content);
				}
			}
		}

		if ($context !== 'post') {
			$content = str_replace('<', '&lt;', $content);
			$content = str_replace('>', '&gt;', $content);
		}

		Logger::info("Took ".(sprintf('%.4f',microtime(true) - $startTime))." seconds.", [], __METHOD__, __LINE__);

		return $content;
	}


	/**
	 * @param $url
	 * @param string $type
	 *
	 * @return int|null
	 */
	private function getShortCodeSource($url, $type='Video'): ?int {
		$baseFile = ltrim(parse_url($url, PHP_URL_PATH), '/');

		if (empty($baseFile)) {
			$this->addToReport(null, $type, $url, null, 'Base file is empty.');
			return null;
		}

		// Remove bucket prefix
		if (!in_array(StorageToolSettings::driver(), ['s3', 'google', 'backblaze']) && (strpos($this->settings->prefixFormat, $this->tool->client()->bucket()) !== 0)) {
			if (strpos($baseFile, $this->tool->client()->bucket().'/') === 0) {
				$baseFile = str_replace($this->tool->client()->bucket().'/', '', $baseFile);
			}
		}

		global $wpdb;
		$query = $wpdb->prepare("select post_id from {$wpdb->postmeta} where meta_key='_wp_attached_file' and meta_value = %s", $baseFile);
		$postId = $wpdb->get_var($query);
		if (empty($postId)) {
			$this->addToReport(null, $type, $url, null, 'Could not map URL to post ID with exact match.');

			$query = $wpdb->prepare("select post_id from {$wpdb->postmeta} where meta_key='_wp_attached_file' and meta_value like %s", '%'.$baseFile);
			$postId = $wpdb->get_var($query);
			if (empty($postId)) {
				$this->addToReport(null, $type, $url, null, 'Could not map URL to post ID with like match.');

				return null;
			}
		}

		return $postId;
	}

	/**
	 * @param string $output Video shortcode HTML output.
	 * @param array $atts Array of video shortcode attributes.
	 * @param ?string $video Video file.
	 * @param ?int $post_id Post ID.
	 * @param ?string $library Media library used for the video shortcode.
	 *
	 * @return string
	 * @throws StorageException
	 */
	public function filterVideoShortcode($output, $atts, $video, $post_id, $library) {
		if (isset($atts['src'])) {
			$default_types = wp_get_video_extensions();
			$postId = null;
			$found = false;
			foreach($default_types as $type) {
				if (!empty($atts[$type])) {
					$url = $atts[$type];
					if (strpos($url, '?') !== false) {
						$url  = preg_replace('/(\?.*)/', '', $url);
					}

					$postId = $this->getShortCodeSource($url);
					$found = !empty($postId);

					break;
				}
			}

			if (!$found) {
				$postId = $this->getShortCodeSource($atts['src']);
			}

			if (empty($postId)) {
				$this->addToReport(null, 'Video', $atts['src'], null, "Unable to map URL to post ID");

				return $output;
			}

			$post = get_post($postId);

			$meta = wp_get_attachment_metadata($post->ID);
			$url = $this->tool->getAttachmentURL(null, $post->ID);

			if (!empty($url)) {
				$mime = arrayPath($meta, 'mime_type', $post->post_mime_type);

				$insert = "<source type='{$mime}' src='$url'/>";
				$insert .= "<a href='{$url}'>{$post->post_title}</a>";
				if(preg_match('/<video(?:[^>]*)>((?s).*)<\/video>/m', $output, $matches)) {
					$output = str_replace($matches[1], $insert, $output);
					$this->addToReport($postId, 'Video', $atts['src'], $url);
				} else {
					$this->addToReport($postId, 'Video', $atts['src'], null, "Unable to find src in content html");
				}
			} else {
				$this->addToReport($postId, 'Video', $atts['src'], null, "Attachment URL is null.");
			}
		}

		return $output;
	}

	/**
	 * @param string $output Audio shortcode HTML output.
	 * @param array $atts Array of audio shortcode attributes.
	 * @param ?string $audio Audio file.
	 * @param ?int $post_id Post ID.
	 * @param ?string $library Media library used for the audio shortcode.
	 *
	 * @return string
	 * @throws StorageException
	 */
	public function filterAudioShortcode($output, $atts, $audio, $post_id, $library) {
		if (isset($atts['src'])) {
			$default_types = wp_get_audio_extensions();
			$postId = null;
			$found = false;
			foreach($default_types as $type) {
				if (!empty($atts[$type])) {
					$url = $atts[$type];
					if (strpos($url, '?') !== false) {
						$url  = preg_replace('/(\?.*)/', '', $url);
					}

					$postId = $this->getShortCodeSource($url, 'Audio');
					$found = !empty($postId);

					break;
				}
			}

			if (!$found) {
				$postId = $this->getShortCodeSource($atts['src'], 'Audio');
			}

			if (empty($postId)) {
				$this->addToReport(null, 'Audio', $atts['src'], null, "Unable to map URL to post ID");

				return $output;
			}

			$post = get_post($postId);

			$meta = wp_get_attachment_metadata($post->ID);
			$url = $this->tool->getAttachmentURL(null, $post->ID);

			if (!empty($url)) {
				$mime = arrayPath($meta, 'mime_type', $post->post_mime_type);

				$insert = "<source type='{$mime}' src='$url'/>";
				$insert .= "<a href='{$url}'>{$post->post_title}</a>";
				if(preg_match('/<audio(?:[^>]*)>((?s).*)<\/audio>/m', $output, $matches)) {
					$output = str_replace($matches[1], $insert, $output);
					$this->addToReport($postId, 'Audio', $atts['src'], $url);
				} else {
					$this->addToReport($postId, 'Audio', $atts['src'], null, "Unable to find URL in content HTML");
				}
			} else {
				$this->addToReport($postId, 'Audio', $atts['src'], null, "Attachment URL is empty.");
			}
		}

		return $output;
	}

	private function generateSrcSet($id, $sizeName): string {
		if ($this->allSizes === null) {
			$this->allSizes = ilab_get_image_sizes();
		}

		if (!is_string($sizeName)) {
			return '';
		}

		if (($sizeName !== 'full') && !isset($this->allSizes[$sizeName])) {
			return '';
		}

		$meta = wp_get_attachment_metadata($id);
		$w = empty($meta['width']) ? (int)0 : (int)$meta['width'];
		$h = empty($meta['height']) ? (int)0 : (int)$meta['height'];
		if (!isset($meta['sizes']) || empty($w) || empty($h)) {
			return '';
		}

		if ($sizeName === 'full') {
			$size = [
				'width' => $w,
				'height' => $h,
				'crop' => false
			];
		} else {
			$size = $this->allSizes[$sizeName];
		}

		$cropped = !empty($size['crop']);

		$sw = empty($size['width']) ? (int)0 : (int)$size['width'];
		$sh = empty($size['height']) ? (int)0 : (int)$size['height'];
		if ($cropped && (empty($sw) || empty($sh))) {
			return '';
		}

		if ($cropped) {
			$filteredSizes = array_filter($this->allSizes, function($v, $k) use ($meta, $sw, $sh) {
				if (empty($v['crop'])) {
					return false;
				}

				$nsw = empty($v['width']) ? (int)0 : (int)$v['width'];
				$nsh = empty($v['height']) ? (int)0 : (int)$v['height'];

				if (empty($nsw) || empty($nsh)) {
					return false;
				}

				$nratio = floor(($nsw / $nsh) * 10);
				$sratio = floor(($sw / $sh) * 10);

				return ((($k === 'full') || isset($meta['sizes'][$k])) && ($nratio === $sratio) && ($nsw <= $sw));
			}, ARRAY_FILTER_USE_BOTH);
		} else {
			$currentSize = sizeToFitSize($w, $h, $sw, $sh);
			$filteredSizes = array_filter($this->allSizes, function($v, $k) use ($meta, $currentSize, $w, $h, $sw, $sh) {
				$nsw = empty($v['width']) ? (int)0 : (int)$v['width'];
				$nsh = empty($v['height']) ? (int)0 : (int)$v['height'];

				if ($nsw === 0) {
					$nsw = 100000;
				}

				if ($nsh === 0) {
					$nsh = 100000;
				}

				$newSize = sizeToFitSize($w, $h, $nsw, $nsh);

				return ((($k === 'full') || isset($meta['sizes'][$k])) && empty($v['crop']) && ($newSize[0] <= $currentSize[0]) && ($newSize[1] <= $currentSize[1]));
			}, ARRAY_FILTER_USE_BOTH);
		}

		$sortedFilteredSizes = $filteredSizes;
		uksort($sortedFilteredSizes, function($a, $b) use ($meta) {
			$aw = (int)$meta['sizes'][$a]['width'];
			$bw = (int)$meta['sizes'][$b]['width'];

			if ($aw === $bw) {
				return 0;
			}

			return ($aw < $bw) ? -1 : 1;
		});

		if ($sizeName === 'full') {
			$sortedFilteredSizes['full'] = $size;
		}

		if (count($sortedFilteredSizes) <= 1) {
			return '';
		}

		$sources = [];

		foreach($sortedFilteredSizes as $name => $sizeInfo) {
			$csize = ($name === 'full') ? $size : $meta['sizes'][$name];

			$sw = (int)$csize['width'];

			if ($name != $sizeName) {
				$sizeKey = "(max-width: {$sw}px) {$sw}px";
			} else {
				$sizeKey = "100vw";
			}

			$src = wp_get_attachment_image_src($id, $name);
			if (!empty($src)) {
				$sources[$sizeKey] = $src[0]." {$sw}w";
			}
		}


		if (!empty($sources)) {
			$sizes = "(max-width: {$sw}px) 100vw, {$sw}px"; //implode(', ', array_keys($sources));
			$srcset = implode(', ', array_values($sources));

			return "srcset='$srcset' sizes='$sizes'";
		}

		return '';
	}

	/**
	 * @param $id
	 * @param $data
	 * @param $content
	 *
	 * @return string|string[]
	 * @throws \MediaCloud\Plugin\Tools\Storage\StorageException
	 */
	private function replaceImageInContent($id, $data, $content) {
		$id = apply_filters('wpml_object_id', $id, 'attachment', true);
		if (empty($data['size'])) {
			$meta = wp_get_attachment_metadata($id);
			$url = $this->tool->getAttachmentURLFromMeta($meta);
			$srcSet = '';
		} else {
			$url = image_downsize($id, $data['size']);
			$srcSet = empty($data['image']) ? '' : $this->generateSrcSet($id, $data['size']);
		}

		if ($url === false) {
			$siteId = apply_filters('global_media.site_id', false);
			if ($siteId != false) {
				switch_to_blog($siteId);
				$url = image_downsize($id, $data['size']);
				restore_current_blog();
			}
		}

		if (is_array($url)) {
			$url = $url[0];
		}

		$url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $url);

		if (empty($url) || (($url == $data['src']) && (empty($srcSet)))) {
			$this->addToReport($id, 'Image', $data['src'], null, 'Unable to map URL');

			return $content;
		}

		if (!empty($data['image']) && $this->settings->replaceSrcSet) {
			$image = $data['image'];
			$image = preg_replace('/(sizes\s*=\s*[\'"]{1}(?:[^\'"]*)[\'"]{1})/m', '', $image);
			$image = preg_replace('/(srcset\s*=\s*[\'"]{1}(?:[^\'"]*)[\'"]{1})/m', '', $image);

			if (!empty($srcSet)) {
				$image = str_replace('<img ', "<img $srcSet ", $image);
			}

			$content = str_replace($data['image'], $image, $content);
		}

		$this->addToReport($id, 'Image', $data['src'], $url);

		return str_replace($data['src'], $url, $content);
	}

	//endregion

	//region Srcset

	private function getAspectRatio($width, $height) {
		if ($width < $height) {
			return 0;
		} else if ($width == $height) {
			return 1;
		} else {
			return 2;
		}
	}

	/**
	 * Filters an image’s ‘srcset’ sources.  (https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/media.php#L1203)
	 *
	 * @param array $sources
	 * @param array $size_array
	 * @param string $image_src
	 * @param array $image_meta
	 * @param int $attachment_id
	 *
	 * @return array
	 */
	public function calculateSrcSet($sources, $size_array, $image_src, $image_meta, $attachment_id) {
		if (!apply_filters('media-cloud/storage/can-calculate-srcset', true)) {
			return $sources;
		}

		global $wp_current_filter;
		if (in_array('the_content', $wp_current_filter)) {
			if ($this->settings->disableSrcSet || $this->settings->replaceSrcSet) {
				return [];
			}
		}

		if ($this->settings->disableSrcSet) {
			return [];
		}

		Logger::info('calculateSrcSet start: '.json_encode($sources, JSON_PRETTY_PRINT), [], __METHOD__, __LINE__);

		$attachment_id = apply_filters('wpml_object_id', $attachment_id, 'attachment', true);

		if (empty($this->allSizes)) {
			$this->allSizes = ilab_get_image_sizes();
		}

		$srcAspect = $this->getAspectRatio($size_array[0], $size_array[1]);

		$imageWidth = intval($image_meta['width']);
		$imageHeight = intval($image_meta['height']);

		if (anyEmpty($imageWidth, $imageHeight)) {
			return [];
		}

		$allSizesNames = array_keys($this->allSizes);
		$newSources = [];

		foreach($image_meta['sizes'] as $sizeName => $sizeData) {
			if (!isset($this->allSizes[$sizeName])) {
				continue;
			}

			$width = intval($this->allSizes[$sizeName]['width']);
			$height = intval($this->allSizes[$sizeName]['height']);

			$width = ($width === 0) ? 99999 : $width;
			$height = ($height === 0) ? 99999 : $height;

			if (empty($this->allSizes[$sizeName]['crop'])) {
				$sizeDim = sizeToFitSize($imageWidth, $imageHeight, $width, $height);
			} else {
				$sizeDim = [$width, $height];
			}

			$sizeAspect = $this->getAspectRatio($sizeDim[0], $sizeDim[1]);

			if (isset($sources["{$sizeDim[0]}"]) && ($sizeAspect == $srcAspect) && in_array($sizeName, $allSizesNames)) {
				$src = wp_get_attachment_image_src($attachment_id, $sizeName);

				if(is_array($src)) {
					// fix for wpml
					$url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $src[0]);
					$newSources["{$sizeDim[0]}"] = $sources["{$sizeDim[0]}"];
					$newSources["{$sizeDim[0]}"]['url'] = $url;
				}
			}
		}

		$imageAspect = $this->getAspectRatio($imageWidth, $imageHeight);

		if(isset($sources["{$imageWidth}"]) && ($imageAspect == $srcAspect)) {
			$src = wp_get_attachment_image_src($attachment_id, 'full');

			if(is_array($src)) {
				// fix for wpml
				$url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $src[0]);
				$newSources["{$imageWidth}"] = $sources["{$imageWidth}"];
				$newSources["{$imageWidth}"]['url'] = $url;
			}
		}

		Logger::info('calculateSrcSet end: '.json_encode($newSources, JSON_PRETTY_PRINT), [], __METHOD__, __LINE__);


		return $newSources;
	}
	//endregion
}