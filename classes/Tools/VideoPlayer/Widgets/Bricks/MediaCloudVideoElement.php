<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Widgets\Bricks;

use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerTool;
use function MediaCloud\Plugin\Utilities\arrayPath;

class MediaCloudVideoElement extends \Bricks\Element {
	public $category     = 'Media Cloud';
	public $name         = 'Media Cloud Video';
	public $icon         = 'fas fa-tv';
	public $css_selector = '';
	public $scripts      = [];
	public $nestable     = false;

	public function get_label() {
		return 'Media Cloud Video';
	}

	public function get_keywords() {
		return ['video', 'media', 'media cloud', 'player'];
	}

	public function set_control_groups() {

	}

	public function set_controls() {
		$this->controls['video'] = [
			'tab'      => 'content',
			'label'    => 'Video',
			'type'     => 'video',
		];

		$this->controls['poster'] = [
			'tab'      => 'content',
			'label'    => 'Poster',
			'type'     => 'image',
		];

		$this->controls['optionsSeparator'] = [
			'label' => 'Player Options',
			'tab'         => 'content',
			'type'        => 'separator',
		];

		$this->controls['autoplay'] = [
			'tab'      => 'content',
			'label'    => 'Auto Play',
			'type'     => 'checkbox',
		];

		$this->controls['loop'] = [
			'tab'      => 'content',
			'label'    => 'Loop',
			'type'     => 'checkbox',
		];

		$this->controls['muted'] = [
			'tab'      => 'content',
			'label'    => 'Muted',
			'type'     => 'checkbox',
		];

		$this->controls['playsinline'] = [
			'tab'      => 'content',
			'label'    => 'Inline',
			'type'     => 'checkbox',
			'default' => true,
		];

		$this->controls['controls'] = [
			'tab'      => 'content',
			'label'    => 'Controls',
			'type'     => 'checkbox',
			'default' => true,
		];

		$this->controls['preload'] = [
			'tab'         => 'content',
			'label'       => 'Preload',
			'type'        => 'select',
			'options'     => [
				'auto' => 'Auto',
				'metadata' => 'Metadata',
				'none' => 'None'
			],
			'placeholder' => 'Metadata',
			'default' => 'metadata',
			'inline'      => true,
		];

		$this->controls['downloadsSeparator'] = [
			'label' => 'Video Downloads',
			'tab'         => 'content',
			'type'        => 'separator',
		];

		$this->controls['allowDownloads'] = [
			'tab'         => 'content',
			'label'       => 'Allow Download',
			'type'        => 'select',
			'options'     => [
				'inherit' => "Use default player settings",
				'no' => "Do not allow download",
				'yes' => "Allow download",
				'logged-in' => "Allow download for logged in users only.",
			],
			'placeholder' => "Use default player settings",
			'default' => 'inherit',
			'inline'      => true,
		];

		$this->controls['allowDownloadsOriginal'] = [
			'tab'         => 'content',
			'label'       => 'Download Original',
			'type'        => 'select',
			'options'     => [
				'inherit' => "Use default player settings",
				'no' => "Do not allow download",
				'yes' => "Allow download",
				'logged-in' => "Allow download for logged in users only.",
			],
			'placeholder' => "Use default player settings",
			'default' => 'inherit',
			'inline'      => true,
		];

	}

	public function render() {
		if (empty($this->settings['video']['id'])) {
			echo "<div style='padding: 5rem; display: flex; align-items: center; justify-content:center;'>Please select a video</div>";
			return;
		}

		$attachmentId = $this->settings['video']['id'];

		$meta = wp_get_attachment_metadata($attachmentId);
		$playerOverride = apply_filters('media-cloud/video-player/override', null, $attachmentId, $meta, []);
		if (!empty($playerOverride)) {
			$playerOverride = str_replace('<div ', "<div {$this->render_attributes('_root')} ", $playerOverride);
			echo $playerOverride;
			return;
		}

		if (empty($this->settings['poster'])) {
			$posterUrl = get_the_post_thumbnail_url($attachmentId, 'full');
		} else {
			$posterUrl = wp_get_attachment_image_url($this->settings['poster']['id'], 'full');
		}

		$videoAttrs = [];
		if ($this->is_frontend) {
			$videoAttrs['autoplay'] = arrayPath($this->settings, 'autoplay', false) === true;
			$videoAttrs['loop'] = arrayPath($this->settings, 'loop', false) === true;
			$videoAttrs['muted'] = arrayPath($this->settings, 'muted', false) === true;
			$videoAttrs['controls'] = arrayPath($this->settings, 'controls', true) === true;
			$videoAttrs['inline'] = arrayPath($this->settings, 'playsinline', true) === true;
			$videoAttrs['preload'] = arrayPath($this->settings, 'preload', 'metadata');
		} else {
			$videoAttrs['autoplay'] = false;
			$videoAttrs['loop'] = false;
			$videoAttrs['muted'] = true;
			$videoAttrs['controls'] = arrayPath($this->settings, 'controls', true) === true;
			$videoAttrs['inline'] = arrayPath($this->settings, 'playsinline', true) === true;
			$videoAttrs['preload'] = 'none';
			$videoAttrs['disabled'] = true;
		}

		$allowDownload = arrayPath($this->settings, 'allowDownloads', 'inherit');
		$allowDownloadOriginal = arrayPath($this->settings, 'allowDownloadsOriginal', 'inherit');

		$id = arrayPath($this->attributes, '_root/id', null);
		$bClasses = arrayPath($this->attributes, '_root/class', []);

		/** @var VideoPlayerTool $playerTool */
		$playerTool = ToolsManager::instance()->tools['video-player'];
		echo $playerTool->renderPlayer( $attachmentId, $videoAttrs, ['allow' => $allowDownload, 'original' => $allowDownloadOriginal], $posterUrl, $bClasses, null, [], $id ?? $this->get_element_attribute_id());
	}

	public static function filterContent($content, $post, $area) {
		$videoSrcRegex = '/<div(?:[^>]+)brxe-video(?:[^>]+)>\s*(<video[^>]+data-src=["\'](.*)[\'"][^>]+\>(?:.*)<\/video>)/mU';
		$pidRegex = '/pid=([0-9]+)/m';
		$posterRegex = '/poster=["\']([^\'"]+)["\']/m';

		preg_match_all($videoSrcRegex, $content, $matches, PREG_SET_ORDER, 0);
		foreach($matches as $match) {
			$videoAttributes = [
				'autoplay' => str_contains($match[1], 'autoplay'),
				'loop' => str_contains($match[1], 'loop'),
				'muted' => str_contains($match[1], 'muted'),
				'inline' => str_contains($match[1], 'playsInline'),
				'controls' => str_contains($match[1], 'controls'),
				'preload' => 'metadata'
			];

			preg_match_all($posterRegex, $match[1], $posterMatches, PREG_SET_ORDER, 0);
			$posterUrl = count($posterMatches) > 0 ? $posterMatches[0][1] : null;

			$player = null;

			if (strpos($match[2], '_mcloud/attachment') !== false) {
				preg_match_all($pidRegex, $match[2], $pidMatches, PREG_SET_ORDER, 0);
				if (count($pidMatches) > 0) {
					$attachmentId = $pidMatches[0][1];

					/** @var VideoPlayerTool $playerTool */
					$playerTool = ToolsManager::instance()->tools['video-player'];
					$player = $playerTool->renderPlayer($attachmentId, $videoAttributes, ['allow' => 'inherit', 'original' => 'inherit'], $posterUrl);
				}
			} else {
				global $wpdb;
				$query = $wpdb->prepare("select id from {$wpdb->prefix}posts where guid = %s", $match[2]);
				$attachmentId = $wpdb->get_var($query);
				if ($attachmentId) {
					/** @var VideoPlayerTool $playerTool */
					$playerTool = ToolsManager::instance()->tools['video-player'];
					$player = $playerTool->renderPlayer($attachmentId, $videoAttributes, ['allow' => 'inherit', 'original' => 'inherit'], $posterUrl);
				}
			}

			if ($player) {
				$div = str_replace(
					[$match[1], '<div ', 'brxe-video'],
					[$player, '<div style="line-height:0" ', ''],
					$match[0]
				);
				$content = str_replace($match[0], $div, $content);
			}
		}

		return $content;
	}
}