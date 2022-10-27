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

namespace MediaCloud\Plugin\Tools\Video\Player\Tool;

use Elementor\Elements_Manager;
use Elementor\Plugin;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Data\MuxDatabase;
use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset;
use MediaCloud\Plugin\Tools\Video\Player\Elementor\MediaCloudVideoWidget;
use MediaCloud\Plugin\Tools\Video\Player\VideoPlayerShortcode;
use MediaCloud\Plugin\Tools\Video\Player\VideoPlayerToolSettings;
use MediaCloud\Plugin\Utilities\UI\CSSColorParser;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\arrayPathExists;
use function MediaCloud\Plugin\Utilities\gen_uuid;
use function MediaCloud\Plugin\Utilities\postIdExists;

class VideoPlayerTool extends Tool {
	/** @var null|VideoPlayerToolSettings|VideoPlayerToolProSettings */
	protected $settings = null;

	/** @var null|VideoPlayerShortcode */
	protected $shortCode = null;

	protected static $enqueued = false;

	public function __construct($toolName, $toolInfo, $toolManager) {
		$this->settings = VideoPlayerToolSettings::instance();

		parent::__construct($toolName, $toolInfo, $toolManager);
	}

	//region Tool Overrides
	public function hasSettings() {
		return true;
	}

	public function setup() {
		if ($this->enabled()) {
			MuxDatabase::init();

			$this->shortCode = new VideoPlayerShortcode();

			add_filter('render_block', [$this, 'filterBlocks'], PHP_INT_MAX - 1, 2);

			if (is_admin() || !empty($this->settings->alwaysIncludeJS) || class_exists('Elementor\Plugin')) {
				static::enqueuePlayer(is_admin());
			} else if (isset($_GET['elementor-preview'])) {
				static::enqueuePlayer(false);
			}

			if (is_admin()) {
				add_action('admin_enqueue_scripts', function(){
					wp_enqueue_script('mux-admin-js', ILAB_PUB_JS_URL.'/mux-admin.js', null, null, true);
					wp_enqueue_style('mux-admin-css', ILAB_PUB_CSS_URL . '/mux-admin.css' );
				});


				$this->integrateWithAdmin();
			}

			$this->initBlocks();
			$this->initShortcodeOverride();
		}
	}
	//endregion

	//region UI
	private function integrateWithAdmin() {
	}
	//endregion

	//region Video Properties
	public function videoPlayerProps($attachmentId, $block = []) {
		if (empty($attachmentId) || !postIdExists($attachmentId)) {
			return null;
		}

		$asset = null;
		$renditionUrl = null;
		$playlistUrl = null;
		$originalUrl = wp_get_attachment_url($attachmentId);
		$playlistMime = 'video/mp4';
		$asset = MuxAsset::assetForAttachment($attachmentId);

		if ($asset && $asset->isTransferred && !empty($asset->transferData)) {
			if ($asset->transferData['source'] === 's3') {
				/** @var StorageTool $storageTool */
				$storageTool = ToolsManager::instance()->tools['storage'];
				$playlistUrl = $storageTool->getAttachmentURLFromMeta(['type' => 'application/vnd.apple.mpegurl', 's3' => ['key' => $asset->transferData['playlist'],]]);
				$playlistMime = 'application/vnd.apple.mpegurl';

				if(arrayPathExists($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality)) {
					$renditionKey = arrayPath($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality);
					$renditionUrl = $storageTool->getAttachmentURLFromMeta(['type' => 'video/mp4', 's3' => ['key' => $renditionKey,]]);
				}
			} else {
				$uploadDir = wp_upload_dir();
				$playlistUrl = trailingslashit($uploadDir['baseurl']) . $asset->transferData['playlist'];
				$playlistMime = 'application/vnd.apple.mpegurl';

				if(arrayPathExists($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality)) {
					$renditionUrl = trailingslashit($uploadDir['baseurl']) . arrayPath($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality);
				}
			}
		} else {
			if (!empty($asset)) {
				$renditionUrl = $asset->renditionUrl($this->settings->playerMP4Quality);
				$playlistUrl = $asset->videoUrl();
				$playlistMime = 'application/vnd.apple.mpegurl';
			} else {
				$playlistUrl = $originalUrl;
			}
		}


		$classes = "mux-player";
		$extras = "";
		$metadata = [];
		$metadataKey = sanitize_title(gen_uuid(12));
		$meta = wp_get_attachment_metadata($attachmentId);
		if (arrayPath($block, 'attrs/outputDimensions', false)) {
			if (arrayPath($meta, 'width', 0) === 0) {
				$thumbID = get_post_meta($attachmentId, '_thumbnail_id', true);
				if (!empty($thumbID)) {
					$thumbMeta = wp_get_attachment_metadata($thumbID);
					if (!empty($thumbMeta)) {
						$width = arrayPath($thumbMeta, 'width', 0);
						$height = arrayPath($thumbMeta, 'height', 0);
						if (($width !== 0) && ($height !== 0)) {
							$extras .= "data-aspect-ratio='{$width}:{$height}'";
						}
					}
				}
			}
		}

		if (!empty($this->settings->playerCSSClasses)) {
			$classes .= " {$this->settings->playerCSSClasses}";
		}

		$source = "<source src='{$playlistUrl}' type='{$playlistMime}' />";

		if ($asset) {
			foreach($asset->subtitles as $subtitle) {
				if ($subtitle['local']) {
					$subtitleKind = $subtitle['cc'] ? 'captions' : 'subtitles';
					$source .= "<track label='{$subtitle['name']}' kind='{$subtitleKind}' srclang='{$subtitle['language_code']}'  src='{$subtitle['url']}' />";
				}
			}
		}

		$metadataHTML = null;
		if (!empty($metadata)) {
			$metadataHTML = "<script id='mux-{$metadataKey}' type='application/json'>".json_encode($metadata, JSON_PRETTY_PRINT)."</script>";
		}

		return [
			'classes' => $classes,
			'extras' => $extras,
			'playlistUrl' => $playlistUrl,
			'playlistMime' => $playlistMime,
			'renditionUrl' => $renditionUrl,
			'source' => $source,
			'metadata' => $metadata,
			'metadataHTML' => $metadataHTML,
			'metadataKey' => $metadataKey,
			'asset' => $asset,
		];
	}
	//endregion

	//region Shortcode Override
	private function initShortcodeOverride() {
		if (!is_admin() && $this->settings->playerOverrideShortcode && ($this->settings->playerType !== 'none')) {
			add_filter( 'wp_video_shortcode', function($output, $atts, $video, $post_id, $library) {
				if (!isset($atts['mp4'])) {
					return $output;
				}

				$attachmentId = attachment_url_to_postid($atts['mp4']);
				if (empty($attachmentId)) {
					return $output;
				}

				[
					'classes' => $classes,
					'extras' => $extras,
					'source' => $source,
					'metadataHTML' => $metadataHTML,
					'metadataKey' => $metadataKey
				] = $this->videoPlayerProps($attachmentId);

				$output = str_replace('class="wp-video-shortcode"', "class='{$classes}' $extras ", $output);

				$sourceRegex = '/<source(?:[^>]+)\>/m';

				$output = preg_replace($sourceRegex, $source, $output);

				if (!empty($metadataHTML)) {
					$output .= "\n".$metadataHTML;
				}

				return $output;

			}, PHP_INT_MAX, 5);
		}
	}
	//endregion

	//region Player
	public static function enqueuePlayer($admin = false) {
		if (static::$enqueued) {
			return;
		}

		static::$enqueued = true;

		add_action((!empty($admin)) ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function() {
			wp_enqueue_script('mux_video_player_hlsjs', ILAB_PUB_JS_URL . '/mux-hls.js', null, null, true);
		});
	}
	//endregion

	//region Blocks
	protected function initBlocks() {
		add_action('init', function() {
			register_block_type( ILAB_BLOCKS_DIR . '/mediacloud-video-block' );
		});

		add_filter('block_categories_all', function($categories, $post) {
			foreach($categories as $category) {
				if ($category['slug'] === 'mediacloud') {
					return $categories;
				}
			}

			$categories[] = [
				'slug' => 'mediacloud',
				'title' => 'Media Cloud',
				'icon' => null
			];

			return $categories;
		}, 10, 2);


		if (class_exists('Elementor\Plugin')) {
			add_action('elementor/widgets/widgets_registered', function() {
				Plugin::instance()->widgets_manager->register(new MediaCloudVideoWidget());
			});

			add_action('elementor/elements/categories_registered', function($elementsManager) {
				/** @var Elements_Manager $elementsManager */
				$elementsManager->add_category('media-cloud', [
					'title' => 'Media Cloud',
					'icon' => 'fa fa-plug'
				]);
			}, 10, 1);

			add_filter('the_content', function($content) {
				return MediaCloudVideoWidget::filterContent($content);
			}, PHP_INT_MAX, 1);

			add_action('wp_enqueue_scripts', function() {
				wp_enqueue_style('mcloud-elementor', trailingslashit(ILAB_PUB_CSS_URL).'mcloud-elementor.css', [], MEDIA_CLOUD_VERSION);
			});
		}
	}
	//endregion

	//region Content Filters
	/**
	 * Filters the File block to include the goddamn attachment ID
	 *
	 * @param $block_content
	 * @param $block
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	function filterBlocks($block_content, $block) {
		if (isset($block['blockName'])) {
			if ($block['blockName'] === 'media-cloud/mux-video-block') {
				return $this->filterVideoBlock($block_content, $block);
			}
		}

		return $block_content;
	}

	protected function filterVideoBlock($block_content, $block) {
		$attachmentId = arrayPath($block, 'attrs/id', null);
		if (empty($attachmentId) || !postIdExists($attachmentId)) {
			return '';
		}

		$asset = null;
		$renditionUrl = null;
		$playlistUrl = null;
		$originalUrl = wp_get_attachment_url($attachmentId);
		$playlistMime = 'video/mp4';
		$muxId = arrayPath($block, 'attrs/muxId', null);
		if (!empty($muxId)) {
			$asset = MuxAsset::asset($muxId);
		}

		if (!$asset) {
			$asset = MuxAsset::assetForAttachment($attachmentId);
		}

		static::enqueuePlayer(is_admin());

		if ($asset && !empty($asset->transferData)) {
			if ($asset->transferData['source'] === 's3') {
				/** @var StorageTool $storageTool */
				$storageTool = ToolsManager::instance()->tools['storage'];
				$playlistUrl = $storageTool->getAttachmentURLFromMeta(['type' => 'application/vnd.apple.mpegurl', 's3' => ['key' => $asset->transferData['playlist'],]]);
				$playlistMime = 'application/vnd.apple.mpegurl';

				if(arrayPathExists($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality)) {
					$renditionKey = arrayPath($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality);
					$renditionUrl = $storageTool->getAttachmentURLFromMeta(['type' => 'video/mp4', 's3' => ['key' => $renditionKey,]]);
				}
			} else {
				$uploadDir = wp_upload_dir();
				$playlistUrl = trailingslashit($uploadDir['baseurl']) . $asset->transferData['playlist'];
				$playlistMime = 'application/vnd.apple.mpegurl';

				if(arrayPathExists($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality)) {
					$renditionUrl = trailingslashit($uploadDir['baseurl']) . arrayPath($asset->transferData, 'renditions/' . $this->settings->playerMP4Quality);
				}
			}
		} else {
			if (!empty($asset)) {
				$renditionUrl = $asset->renditionUrl($this->settings->playerMP4Quality);
				$playlistUrl = $asset->videoUrl();
				$playlistMime = 'application/vnd.apple.mpegurl';
			} else {
				$playlistUrl = $originalUrl;
			}
		}


		$classes = "mux-player";
		$extras = "";
		$metadata = [];
		$metadataKey = sanitize_title(gen_uuid(12));
		$meta = wp_get_attachment_metadata($attachmentId);
		if (arrayPath($block, 'attrs/outputDimensions', false)) {
			if (arrayPath($meta, 'width', 0) === 0) {
				$thumbID = get_post_meta($attachmentId, '_thumbnail_id', true);
				if (!empty($thumbID)) {
					$thumbMeta = wp_get_attachment_metadata($thumbID);
					if (!empty($thumbMeta)) {
						$width = arrayPath($thumbMeta, 'width', 0);
						$height = arrayPath($thumbMeta, 'height', 0);
						if (($width !== 0) && ($height !== 0)) {
							$extras .= "data-aspect-ratio='{$width}:{$height}'";
						}
					}
				}
			}
		}

		if (!empty($this->settings->playerCSSClasses)) {
			$classes .= " {$this->settings->playerCSSClasses}";
		}

		$block_content = str_replace('<video ', "<video class='{$classes}' {$extras}", $block_content);

		$source = "<source src='{$playlistUrl}' type='{$playlistMime}' />";

		if ($asset) {
			foreach($asset->subtitles as $subtitle) {
				if ($subtitle['local']) {
					$subtitleKind = $subtitle['cc'] ? 'captions' : 'subtitles';
					$source .= "<track label='{$subtitle['name']}' kind='{$subtitleKind}' srclang='{$subtitle['language_code']}'  src='{$subtitle['url']}' />";
				}
			}
		}

		$block_content = str_replace('<source/>', $source, $block_content);

		if (!empty($metadata)) {
			$metadataHTML = "<script id='mux-{$metadataKey}' type='application/json'>".json_encode($metadata, JSON_PRETTY_PRINT)."</script>";
			$block_content .= "\n".$metadataHTML;
		}

		return $block_content;
	}
	//endregion
}
