<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\VideoJS;

use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerInterface;
use MediaCloud\Plugin\Utilities\UI\CSSColorParser;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\gen_uuid;

class VideoJSPlayer implements VideoPlayerInterface {
	/** @var VideoJSPlayerSettings */
	protected $settings;

	protected $enqueued = false;

	public function __construct() {
		$this->settings = VideoJSPlayerSettings::instance();
	}

	public function enqueueScripts($admin) {
		if ($this->enqueued) {
			return;
		}

		$this->enqueued = true;

		add_action(!empty($admin) ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function() {
			$lastDep = 'mux_videojs';

			if(!empty($this->settings->envKey)) {
				wp_enqueue_script('mux_videojs', ILAB_PUB_JS_URL . '/videojs-player-data.js', null, null, true);
			} else {
				wp_enqueue_script('mux_videojs', ILAB_PUB_JS_URL . '/videojs-player.js', null, null, true);
			}

			$playerSettings = ['plugins' => []];

			if($this->settings->playerAllowAirPlay) {
				$playerSettings['plugins']['airPlay'] = ['addButtonToControlBar' => true];
			}

			if($this->settings->playerAllowQualitySelect) {
				$playerSettings['plugins']['httpSourceSelector'] = ['default' => 'auto'];
			}

			$lastDep = apply_filters('media-cloud/video/videojs-enqueue-plugins', $lastDep);

			if (empty($this->settings->playerOverrideNative)) {
				$playerSettings['html5'] = [
					'vhs' => [
						'overrideNative' => false
					],
					'nativeAudioTracks' => true,
					'nativeVideoTracks' => true,
				];
			} else {
				$playerSettings['html5'] = [
					'vhs' => [
						'overrideNative' => true
					],
					'nativeAudioTracks' => false,
					'nativeVideoTracks' => false,
				];
			}

			$playerSettings = apply_filters('media-cloud/video/videojs-player-settings', $playerSettings);

			$deps = empty($lastDep) ? null: [$lastDep];
			wp_enqueue_script('mux_video_player_videojs', ILAB_PUB_JS_URL . '/mux-player.js', $deps, null, true);
			wp_localize_script('mux_video_player_videojs', 'muxPlayerSettings', $playerSettings);

			wp_enqueue_style('mux_video_player_style', ILAB_PUB_CSS_URL . '/mux-player.css', null, null);
			do_action('media-cloud/video/videojs-enqueue-style', 'mux_video_player_style');

			if ($this->settings->customizeColors) {
				add_action('wp_footer', function() {
					$primaryColor = CSSColorParser::array($this->settings->primaryColor);
					$loadProgressColor = CSSColorParser::array($this->settings->loadProgressBarColor);
					echo <<<CSS
								<style>
									:root {
										--vjs-controlbar-background-color: rgba({$primaryColor[0]}, {$primaryColor[1]}, {$primaryColor[2]}, 0.7);
									}
									
									.vjs-button {
										color: {$this->settings->buttonColor} !important;
									}
									
									.vjs-play-progress {
										background-color: {$this->settings->playbackBarColor} !important;
										color: {$this->settings->playbackBallColor} !important;
									}
									
									.vjs-load-progress {
										background: rgba({$loadProgressColor[0]}, {$loadProgressColor[1]}, {$loadProgressColor[2]}, 0.5) !important;
									}
								</style>
							CSS;
				}, PHP_INT_MAX - 100);
			}
		});
	}

	public function renderPlayer(
		$attachmentId,
		$playerData,
		$videoAttributes = ['inline' => true, 'controls' => true],
		$downloads = ['allow' =>  'inherit', 'original' => 'inherit'],
		$posterUrl = null,
		$videoId = null,
		$videoClasses = [],
		$videoStyles = [],
		$wrapperEle = null,
		$wrapperClasses = [],
		$wrapperStyles = []
	) {
		$this->enqueueScripts(is_admin());

		$tagAttributeList = [];

		if (!empty(arrayPath($videoAttributes, 'autoplay', false))) {
			$tagAttributeList[] = 'autoplay';
		}

		if (!empty(arrayPath($videoAttributes, 'loop', false))) {
			$tagAttributeList[] = 'loop';
		}

		if (!empty(arrayPath($videoAttributes, 'muted', false))) {
			$tagAttributeList[] = 'muted';
		}

		if (!empty(arrayPath($videoAttributes, 'controls', false))) {
			$tagAttributeList[] = 'controls';
		}

		if (!empty(arrayPath($videoAttributes, 'inline', false))) {
			$tagAttributeList[] = 'playsInline';
		}

		if (!empty(arrayPath($videoAttributes, 'preload', null))) {
			$preload = arrayPath($videoAttributes, 'preload', null);
			if ($preload !== null && in_array(strtolower($preload), ['auto', 'metadata', 'none'])) {
				$tagAttributeList[] = "preload='".strtolower($preload)."'";
			}
		}

		$allowDownload = arrayPath($downloads, 'allow', 'no');
		$allowDownloadOriginal = arrayPath($downloads, 'original', 'no');

		$tagAttributes = implode(' ', $tagAttributeList);

		$sources = [];
		if ($playerData->playlistUrl) {
			$sources[] = "<source type='application/x-mpegURL' src={$playerData->playlistUrl} />";
		}

		if ($playerData->renditionUrl) {
			$sources[] = "<source type='video/mp4' src={$playerData->renditionUrl} />";
		}

		if (!empty($playerData->subtitles)) {
			foreach($playerData->subtitles as $subtitle) {
				$sources[] = "<source type='text/vtt' src='{$subtitle['url']}' />";
			}
		}
		$sourcesText = implode("\n", $sources);

		$videoIdAttr = $videoId ? "id='{$videoId}'" : "";

		$videoStyles = array_merge([
			'width' => '100%',
			'aspect-ratio' => "{$playerData->width}/{$playerData->height}",
		], $videoStyles);

		$vstyles = array_map(function($k, $v) {
			return "$k: $v";
		}, array_keys($videoStyles), array_values($videoStyles));
		$videoStylesAttr = count($vstyles) > 0 ? "style='".implode("; ", $vstyles)."'" : "";


		$videoClasses = array_merge(['mcloud-video','mux-player','video-js'], $videoClasses);
		if (!empty($this->settings->playerCSSClasses)) {
			$videoClasses = array_merge(explode(' ', $this->settings->playerCSSClasses), $videoClasses);
		}
		$videoClassAttr = count($videoClasses) > 0 ? "class='".implode(" ", $videoClasses)."'": "";

		$wrapperTag = '';
		$wrapperClosingTag = '';
		if ($wrapperEle) {
			$styles = array_map(function($k, $v) {
				return "$k: $v";
			}, array_keys($wrapperStyles), array_values($wrapperStyles));

			$stylesAttr = count($styles) > 0 ? "style='".implode("; ", $styles)."'" : "";
			$classAttr = count($wrapperClasses) > 0 ? "class='".implode(" ", $wrapperClasses)."'": "";

			$wrapperTag = "<$wrapperEle $classAttr $stylesAttr>";
			$wrapperClosingTag = "</$wrapperEle>";
		}

		$extras = "";
		$metadata = [];
		$metadataKey = sanitize_title(gen_uuid(12));
		if ($playerData->width > 0 && $playerData->height > 0) {
			$extras .= "data-aspect-ratio='{$playerData->width}:{$playerData->height}'";
		}

		if($this->settings->playerFilmstrips) {
			if(!empty($playerData->filmstripUrl) && $playerData->duration > 0) {
				$extras .= " data-filmstrip='{$playerData->filmstripUrl}'";
				$frames = min(floor($playerData->duration) + 1, 15);
				$interval = sprintf('%0.2f', $playerData->duration / $frames);
				$extras .= " data-filmstrip-interval='{$interval}'";
			}
		}

		if (!empty($playerData->extraMetadata)) {
			if(!empty($this->settings->envKey)) {
				$extras .= " data-metadata-key='{$metadataKey}'";
				$metadata = $playerData->extraMetadata;
			}

			if (!empty($this->settings->playerAnalyticsMode) && ($this->settings->playerAnalyticsMode != 'none')) {
				$extras .= " data-analytics='{$this->settings->playerAnalyticsMode}'";
				$extras .= " data-analytics-asset='{$attachmentId}-{$playerData->title}'";
			}
		}

		$playerAllowDownload = $this->settings->playerAllowDownload;
		$playerAllowOriginalDownload = $this->settings->playerAllowOriginalDownload;

		if ($allowDownload !== 'inherit') {
			if ($allowDownload === 'yes') {
				$playerAllowDownload = true;
			} else if ($allowDownload === 'no') {
				$playerAllowDownload = false;
			} else {
				$playerAllowDownload = 'logged-in';
			}
		}

		if ($allowDownloadOriginal !== 'inherit') {
			if ($allowDownloadOriginal === 'yes') {
				$playerAllowOriginalDownload = true;
			} else if ($allowDownload === 'no') {
				$playerAllowOriginalDownload = false;
			} else {
				$playerAllowOriginalDownload = 'logged-in';
			}
		}

		$downloadUrl = null;
		if (!empty($playerAllowDownload) || !empty($playerAllowOriginalDownload)) {
			if (!empty($playerAllowDownload)) {
				if ($playerAllowDownload === 'logged-in') {
					if (is_user_logged_in()) {
						$downloadUrl = $playerData->renditionUrl;
					}
				} else {
					$downloadUrl = $playerData->renditionUrl;
				}
			} else {
				if ($playerAllowOriginalDownload === 'logged-in') {
					if (is_user_logged_in()) {
						$downloadUrl = $playerData->originalUrl;
					}
				} else {
					$downloadUrl = $playerData->originalUrl;
				}
			}
		}

		if (!empty($downloadUrl)) {
			$extras .= " data-download-url='{$downloadUrl}'";
		}


		if (!empty($posterUrl)) {
			$extras .= " poster='{$posterUrl}'";
		}


		$block_content = "$wrapperTag<video {$videoIdAttr} {$tagAttributes} {$videoStylesAttr} {$videoClassAttr} {$extras}>{$sourcesText}</video>{$wrapperClosingTag}";
		if (!empty($metadata)) {
			$metadataHTML = "<script id='mux-{$metadataKey}' type='application/json'>".json_encode($metadata, JSON_PRETTY_PRINT)."</script>";
			$block_content .= "\n".$metadataHTML;
		}

		return $block_content;
	}

	static function registerSettings($optionsPage) {}
}