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

namespace MediaCloud\Plugin\Tools\VideoPlayer\Widgets\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerTool;
use function MediaCloud\Plugin\Utilities\arrayPath;

class MediaCloudVideoWidget extends Widget_Base {
	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
	}

	public function get_name() {
		return "mux-video";
	}

	public function get_title() {
		return "Media Cloud Video";
	}

	public function get_icon() {
		return 'eicon-tv';
	}

	public function get_categories() {
		return ['media-cloud'];
	}

	protected function _register_controls() {
		$this->start_controls_section('content_section', [
			'label' => 'Content',
			'tab' => Controls_Manager::TAB_CONTENT
		]);

		$this->add_control('video', [
			'label' => 'Video',
			'media_type' => 'video',
			'type' => Controls_Manager::MEDIA,
		]);

		$this->add_control('poster', [
			'label' => 'Poster Image',
			'media_type' => 'image',
			'separator' => 'before',
			'type' => Controls_Manager::MEDIA,
		]);


		$this->add_control('autoplay', [
			'label' => 'Auto Play',
			'type' => Controls_Manager::SWITCHER,
			'separator' => 'before',
		]);


		$this->add_control('loop', [
			'label' => 'Loop',
			'type' => Controls_Manager::SWITCHER,
		]);

		$this->add_control('muted', [
			'label' => 'Muted',
			'type' => Controls_Manager::SWITCHER,
		]);

		$this->add_control('playsinline', [
			'label' => 'Play Inline',
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes'
		]);

		$this->add_control('controls', [
			'label' => 'Show Controls',
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes'
		]);

		$this->add_control('preload', [
			'label' => 'Preload',
			'type' => Controls_Manager::SELECT,
			'options' => [
				'auto' => 'Auto',
				'metadata' => 'Metadata',
				'none' => 'None'
			],
			'default' => 'metadata'
		]);

		$this->add_control('allowdownloads', [
			'label' => 'Allow Video Download',
			'type' => Controls_Manager::SELECT,
			'options' => [
				'inherit' => "Use default player settings",
				'no' => "Do not allow download",
				'yes' => "Allow download",
				'logged-in' => "Allow download for logged in users only.",
			],
			'default' => 'inherit'
		]);

		$this->add_control('allowdownloadsoriginal', [
			'label' => 'Allow Original Video Downloads',
			'type' => Controls_Manager::SELECT,
			'options' => [
				'inherit' => "Use default player settings",
				'no' => "Do not allow download",
				'yes' => "Allow download",
				'logged-in' => "Allow download for logged in users only.",
			],
			'default' => 'inherit'
		]);

		$this->end_controls_section();
	}

	private function renderEmpty($message, $hasError = false) {
		$classes = ($hasError) ? 'has-error' : '';

		echo <<<RENDER
<div class="mcloud-elem-empty-video {$classes}"><div>{$message}</div></div>
RENDER;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if (empty($settings['video']['id'])) {
			$this->renderEmpty("Please select a video.");
			return;
		}

		$attachmentId = $settings['video']['id'];

		$posterUrl = null;
		if (empty($settings['poster']['id'])) {
			$posterUrl = get_the_post_thumbnail_url($settings['video']['id'], 'full');
		}

		$videoAttrs = [];

		if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
			$videoAttrs['autoplay'] = false;
			$videoAttrs['loop'] = false;
			$videoAttrs['muted'] = true;
			$videoAttrs['controls'] = arrayPath($settings, 'controls', 'yes') === 'yes';
			$videoAttrs['inline'] = true;
			$videoAttrs['preload'] = 'none';
		} else {
			$videoAttrs['autoplay'] = arrayPath($settings, 'autoplay', 'yes') === 'yes';
			$videoAttrs['loop'] = arrayPath($settings, 'loop', 'yes') === 'yes';
			$videoAttrs['muted'] = arrayPath($settings, 'muted', 'yes') === 'yes';
			$videoAttrs['controls'] = arrayPath($settings, 'controls', 'yes') === 'yes';
			$videoAttrs['inline'] = arrayPath($settings, 'playsinline', 'yes') === 'yes';
			$videoAttrs['preload'] = arrayPath($settings, 'preload', 'metadata');
		}


		$allowDownload = arrayPath($settings, 'allowdownloads', 'inherit');
		$allowDownloadOriginal = arrayPath($settings, 'allowdownloadsoriginal', 'inherit');

		/** @var VideoPlayerTool $playerTool */
		$playerTool = ToolsManager::instance()->tools['video-player'];
		echo "<div style='line-height:0'>".$playerTool->renderPlayer($attachmentId, $videoAttrs,['allow' => $allowDownload, 'original' => $allowDownloadOriginal], $posterUrl, ['mcloud-elementor-video'])."</div>";
	}

	public static function filterContent($content) {
		if (empty(apply_filters('media-cloud/video-encoder/secures-urls', false))) {
			return $content;
		}

		$vidregex = '/<\s*div\s+class=\"\s*mux-ele-video-container(?:[^>]+)>\s*(<video[^>]+>)\s*((<(?:source|track)[^>]+>)+)/ms';
		if (preg_match_all($vidregex, $content, $matches, PREG_SET_ORDER, 0)) {
			foreach($matches as $match) {
				$video = $match[1];
				$currentSource = $match[2];

				if (preg_match_all('/data-attachment-id\s*=\s*(?:\'|")([^\'"]+)/ms', $video, $idMatches)) {
					$attachmentId = $idMatches[1][0];
					/** @var VideoPlayerTool $playerTool */
					$playerTool = ToolsManager::instance()->tools['video-player'];
					['source' => $source] = $playerTool->videoPlayerProps($attachmentId);
					$content = str_replace($currentSource, $source, $content);
				} else if (preg_match_all('/data-mux-id\s*=\s*(?:\'|")([^\'"]+)/ms', $video, $idMatches)) {
					$assetId = $idMatches[1][0];
					$asset = MuxAsset::asset($assetId);
					if (!empty($asset)) {
						$newUrl = $asset->videoUrl();
						$content = str_replace($currentSource, "<source src=\"$newUrl\" type=\"application/x-mpegURL\" />", $content);
					}
				}
			}
		}

		return $content;
	}
}