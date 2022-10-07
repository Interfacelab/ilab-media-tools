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

namespace MediaCloud\Plugin\Tools\Video\Player\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset;
use MediaCloud\Plugin\Tools\Video\Player\Tool\VideoPlayerTool;
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

		/** @var VideoPlayerTool $playerTool */
		$playerTool = ToolsManager::instance()->tools['video-player'];

		[
			'classes' => $classes,
			'extras' => $extras,
			'source' => $source,
			'metadataHTML' => $metadataHTML,
			'metadataKey' => $metadataKey,
			'asset' => $asset
		] = $playerTool->videoPlayerProps($settings['video']['id']);

		$classes .= ' elementor-mux-player';
		$extras .= " data-attachment-id='{$settings['video']['id']}'";

		if (empty($settings['poster']['id'])) {
			$posterUrl = get_the_post_thumbnail_url($settings['video']['id'], 'full');
		} else {
			$posterUrl = wp_get_attachment_image_url($settings['poster']['id'], 'full');
		}

		if (!empty($posterUrl)) {
			$extras .= " poster='{$posterUrl}'";
		}

		if (arrayPath($settings, 'autoplay', 'yes') === 'yes') {
			$extras .= ' autoplay';
		}

		if (arrayPath($settings, 'loop', 'yes') === 'yes') {
			$extras .= ' loop';
		}

		if (arrayPath($settings, 'muted', 'yes') === 'yes') {
			$extras .= ' muted';
		}

		if (arrayPath($settings, 'controls', 'yes') === 'yes') {
			$extras .= ' controls';
		}

		if (arrayPath($settings, 'playsinline', 'yes') === 'yes') {
			$extras .= ' playsinline';
		}

		$preload = arrayPath($settings, 'preload', 'metadata');
		$extras .= " preload='{$preload}'";

		$aspectClass = 'mux-ele-video-container';
		if ($asset) {
			$aspect = generateAspectRatio($asset->width, $asset->height);
			$aspectClass .= ' aspect-'.implode('-', $aspect);
		}

		echo <<<RENDER
		<figure class="{$aspectClass}">
			<video class="{$classes}" {$extras}>
				{$source}
			</video>
			{$metadataHTML}
		</figure>
RENDER;

	}

	public static function filterContent($content) {
		$vidregex = '/<\s*figure\s+class=\"\s*mux-ele-video-container(?:[^>]+)>\s*(<video[^>]+>)\s*((<(?:source|track)[^>]+>)+)/ms';
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