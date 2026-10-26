<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome;

use MediaCloud\Plugin\Tools\Video\Driver\Mux\Models\MuxAsset;
use MediaCloud\Plugin\Tools\Video\VideoEncodingToolSettings;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerData;
use MediaCloud\Plugin\Tools\VideoPlayer\VideoPlayerInterface;
use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\arrayPath;

class MediaChromePlayer implements VideoPlayerInterface {
	/** @var null|string  */
	private static $options_page = null;

	/** @var MediaChromePlayerSettings */
	protected $settings;

	public function __construct() {
		$this->settings = MediaChromePlayerSettings::instance();
	}

	public function enqueueScripts($admin) {
		add_filter( 'wp_script_attributes', function($attributes) {
			if (in_array($attributes['id'], ['mcloud_settings_components_js-js', 'mcloud_media_chrome_js-js', 'mcloud_media_chrome_hls_js-js', 'mcloud_media_chrome_mux_js-js', 'mcloud_media_chrome_cloudflare_js-js'])) {
				$attributes['type'] = 'module';
			}

			return $attributes;
		}, 10, 1 );


		add_action(!empty($admin) ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function() {
			wp_enqueue_script('mcloud_media_chrome_js', 'https://cdn.jsdelivr.net/npm/media-chrome@3/+esm', null, MEDIA_CLOUD_VERSION, true);
			wp_enqueue_script('mcloud_media_chrome_hls_js', 'https://cdn.jsdelivr.net/npm/hls-video-element@1/+esm', null, MEDIA_CLOUD_VERSION, true);
			if (VideoEncodingToolSettings::driver() === 'mux') {
				wp_enqueue_script('mcloud_media_chrome_mux_js', 'https://cdn.jsdelivr.net/npm/@mux/mux-video@0/+esm', null, MEDIA_CLOUD_VERSION, true);
			} else if (VideoEncodingToolSettings::driver() === 'cloudflare-stream') {
				wp_enqueue_script('mcloud_media_chrome_cloudflare_js',  ILAB_PUB_JS_URL.'/cloudflare-video-element.js', null, MEDIA_CLOUD_VERSION, true);
			}
		});

		add_action('wp_head', function() {
			echo $this->settings->mediaChromeConfig->playerStyles();
		}, PHP_INT_MAX);
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

		$storyboardUrl = $playerData->storyboardUrl;
		$playerType = MediaChromeConfig::VIDEO_TYPE_MP4;
		$videoUrl = $playerData->renditionUrl;
		if ($playerData->encoder === 'mux' && $playerData->encoderData && empty($playerData->encoderData->isTransferred)) {
			$playerType = MediaChromeConfig::VIDEO_TYPE_MUX;
			/** @var MuxAsset $asset */
			$asset = $playerData->encoderData;
			if (!empty($asset->securePlaybackID)) {
				$token = $asset->secureToken($asset->securePlaybackID);
				$videoUrl = $asset->securePlaybackID . '?token='.$token;
			} else {
				$videoUrl = $asset->publicPlaybackID;
			}
			$storyboardUrl = $asset->storyboardUrl();
		} else if ($playerData->encoder === 'cloudflare' && !empty($playerData->encoderData)) {
			$playerType = MediaChromeConfig::VIDEO_TYPE_CLOUDFLARE;
			$videoUrl = $playerData->encoderData;
		}  else if (!empty($playerData->playlistUrl)) {
			$playerType = MediaChromeConfig::VIDEO_TYPE_HLS;
			$videoUrl = $playerData->playlistUrl;
		}

		return $wrapperTag.$this->settings->mediaChromeConfig->player(
			$playerType,
			$videoUrl,
			$playerData->width,
			$playerData->height,
			$posterUrl,
			null,
			$videoAttributes,
			$videoId,
			$storyboardUrl
		).$wrapperClosingTag;
	}


	static function registerSettings($optionsPage) {
		static::$options_page = $optionsPage;
		add_action('media-cloud/tools/register-setting-type', [static::class, 'registerMediaChromeControlSettings'], 10, 5);
	}

	//region Settings
	public static function registerMediaChromeControlSettings($option, $optionInfo, $group, $groupInfo, $conditions) {
		if ($optionInfo['type'] === 'media-chrome-controls-editor') {
			if (is_admin()) {
				add_filter( 'wp_script_attributes', function($attributes) {
					if (key_exists('id', $attributes) && in_array($attributes['id'], ['mcloud_settings_components_js-js', 'mcloud_media_chrome_js-js', 'mcloud_media_chrome_hls_js-js', 'mcloud_media_chrome_mux_js-js'])) {
						$attributes['type'] = 'module';
					}

					return $attributes;
				}, 10, 1 );

				wp_enqueue_script('mcloud_media_chrome_js', 'https://cdn.jsdelivr.net/npm/media-chrome@3/+esm', null, MEDIA_CLOUD_VERSION, true);
				wp_enqueue_script('mcloud_media_chrome_hls_js', 'https://cdn.jsdelivr.net/npm/hls-video-element@1/+esm', null, MEDIA_CLOUD_VERSION, true);

				wp_enqueue_script('mcloud_settings_components_js', ILAB_PUB_URL . '/components/settings/assets/js/index.js', null, MEDIA_CLOUD_VERSION, true);
				wp_enqueue_style('mcloud_settings_components_css', ILAB_PUB_URL . '/components/settings/assets/css/index.css', null, MEDIA_CLOUD_VERSION);
			}

			$description = arrayPath($optionInfo,'description',null);

			add_settings_field($option,
				$optionInfo['title'],
				[static::class, 'renderMediaChromeControlSettings'],
				static::$options_page,
				$group,
				['option'=>$option, 'description'=>$description, 'conditions' => $conditions]);
		}
	}

	public static function renderMediaChromeControlSettings($args) {
		$value = Environment::Option($args['option']);
		if (empty($value)) {
			$value = '';
		}

		echo View::render_view('base.fields.media-chrome-controls-editor',[
			'value' => $value,
			'name' => $args['option'],
			'conditions' => $args['conditions'],
			'description' => (isset($args['description'])) ? $args['description'] : false
		]);
	}
	//endregion
}