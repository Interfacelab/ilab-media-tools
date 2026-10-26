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

namespace MediaCloud\Plugin\Tools\VideoPlayer;

use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class VideoPlayerShortcode {
	/** @var VideoPlayerToolSettings|null  */
	private $settings = null;

	/** @var VideoPlayerInterface|null  */
	protected $player = null;

	/**
	 * @param VideoPlayerInterface $player
	 */
	public function __construct($player) {
		$this->player = $player;

		$this->settings = VideoPlayerToolSettings::instance();

		if (is_admin()) {
			global $pagenow, $typenow;
			if (in_array($pagenow, ['post.php', 'page.php', 'post-new.php', 'post-edit.php']) && ($typenow !== 'download')) {
				add_action('media_buttons', [$this, 'addMediaButtons'], 11);
				add_action('admin_footer', function() {
					echo View::render_view('admin.video-shortcode-editor', []);
				});
			}
		}

		add_action('init', function() {
			add_shortcode("mux_video", [$this, 'renderShortCode']);
			add_shortcode("mcloud_video", [$this, 'renderShortCode']);
		});
	}

	public function addMediaButtons() {
		$img = '<span class="wp-media-buttons-icon mux-shortcode-icon" id="edd-media-button"></span>';
		$output = '<a name="Add Media Cloud Video" href="#" class="button mux-shortcode-wizard" style="padding-left: .4em;">' . $img . 'Add Media Cloud Video' . '</a>';

		echo $output;
	}

	public function renderShortCode($attrs) {
		$attachmentId = $attrs['id'];
		if (empty($attachmentId) || !postIdExists($attachmentId)) {
			return '';
		}

		$posterUrl = null;
		if (!empty($attrs['poster'])) {
			$posterUrl = wp_get_attachment_image_url($attrs['poster'], 'full');
		}

		$videoAttrs = [];
		$videoAttrs['autoplay'] = arrayPath($attrs, 'autoplay', 'true') === 'true';
		$videoAttrs['loop'] = arrayPath($attrs, 'loop', 'true') === 'true';
		$videoAttrs['muted'] = arrayPath($attrs, 'muted', 'true') === 'true';
		$videoAttrs['controls'] = arrayPath($attrs, 'controls', 'true') === 'true';
		$videoAttrs['inline'] = arrayPath($attrs, 'inline', 'true') === 'true';
		$videoAttrs['preload'] = arrayPath($attrs, 'preload', 'metadata');

		$allowDownload = arrayPath($attrs, 'allow-download', 'inherit');
		$allowDownloadOriginal = arrayPath($attrs, 'allow-original-download', 'inherit');

		/** @var VideoPlayerTool $playerTool */
		$playerTool = ToolsManager::instance()->tools['video-player'];
		return '<div>'.$playerTool->renderPlayer($attachmentId, $videoAttrs, ['allow' => $allowDownload, 'original' => $allowDownloadOriginal], $posterUrl).'</div>';
	}

}
