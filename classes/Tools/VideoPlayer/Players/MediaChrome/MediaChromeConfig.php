<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome;

use MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO\MediaChromeBranding;
use MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO\MediaChromeColor;
use MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO\MediaChromeControlBar;
use MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO\MediaChromeOptions;
use function MediaCloud\Plugin\Utilities\arrayFind;
use function MediaCloud\Plugin\Utilities\arrayPath;

class MediaChromeConfig {
	const VIDEO_TYPE_HLS = 'hls';
	const VIDEO_TYPE_MUX = 'mux';
	const VIDEO_TYPE_MP4 = 'mp4';
	const VIDEO_TYPE_CLOUDFLARE = 'cloudflare';

	/** @var MediaChromeOptions  */
	public $options;

	/** @var MediaChromeBranding  */
	public $branding;

	/** @var MediaChromeColor[] */
	public $colors;

	/** @var MediaChromeControlBar[]  */
	public $controlBars;

	public function __construct($json = '{}') {
		$data = json_decode($json, true);

		$this->options = new MediaChromeOptions($data['options'] ?? []);
		$this->branding = new MediaChromeBranding($data['branding'] ?? [
			'imageId' => null,
			'imageUrl' => null,
			'padding' => 16,
			'maxHeight' => 64,
			'opacity' => 100,
			'alignment' => 'top-left',
			'autoHide' => false,
			'imageSize' => ['width' => 0, 'height' => 0]
		]);

		$colors = arrayPath($data, 'colors', []);
		$this->colors = [];
		foreach($colors as $color) {
			$this->colors[] = new MediaChromeColor($color);
		}

		$controlBars = arrayPath($data, 'controlBars', [
			['showBg' => false, 'controls' => []]
		]);
		$this->controlBars = [];
		foreach($controlBars as $controlBar) {
			$this->controlBars[] = new MediaChromeControlBar($controlBar);
		}
	}

	public function enqueueScripts($videoType) {
		add_filter( 'wp_script_attributes', function($attributes) {
			if (in_array($attributes['id'], [
				'mcloud_media_chrome_js-js',
				'mcloud_media_chrome_hls_js-js',
				'mcloud_media_chrome_mux_js-js',
				'mcloud_media_chrome_cloudflare_js-js',
			])) {
				$attributes['type'] = 'module';
			}

			return $attributes;
		}, 10, 1 );

		wp_enqueue_script('mcloud_media_chrome_js', 'https://cdn.jsdelivr.net/npm/media-chrome@3/+esm', null, MEDIA_CLOUD_VERSION, true);

		if ($videoType === MediaChromeConfig::VIDEO_TYPE_HLS) {
			wp_enqueue_script('mcloud_media_chrome_hls_js', 'https://cdn.jsdelivr.net/npm/hls-video-element@1/+esm', null, MEDIA_CLOUD_VERSION, true);
		} else if ($videoType === MediaChromeConfig::VIDEO_TYPE_MUX) {
			wp_enqueue_script('mcloud_media_chrome_mux_js', 'https://cdn.jsdelivr.net/npm/@mux/mux-video@0/+esm', null, MEDIA_CLOUD_VERSION, true);
		} else if ($videoType === MediaChromeConfig::VIDEO_TYPE_CLOUDFLARE) {
			wp_enqueue_script('mcloud_media_chrome_cloudflare_js', 'https://cdn.jsdelivr.net/npm/cloudflare-video-element@1/+esm', null, MEDIA_CLOUD_VERSION, true);
		}
	}

	/**
	 * @return string
	 */
	public function playerStyles() {
		$colors = implode("\n", array_map(function($val) {
			$colorVal = $val->color ?? $val->default;
			return "--{$val->id}: {$colorVal};";
		}, $this->colors));

		$branding = '';
		$brandingPaused = '';
		$brandingImage = '';
		if (!empty($this->branding->imageUrl)) {
			$brandingCSS = [];
			$brandingPausedCSS = [];
			$brandingImageCSS = [];

			if (str_starts_with($this->branding->alignment, 'bottom-')) {
				$brandingPausedCSS[] = 'transform: translateY(44px);';
			}

			$brandingCSS[] = "padding: {$this->branding->padding}px;";
			$brandingCSS[] = 'display: flex;';
			$brandingCSS[] = 'box-sizing: border-box;';
			$brandingCSS[] = 'width:100%;';
			switch($this->branding->alignment) {
				case 'top-left':
				case 'bottom-left':
					$brandingCSS[] = 'justify-content: flex-start;';
					break;
				case 'top-right':
				case 'bottom-right':
					$brandingCSS[] = 'justify-content: flex-end;';
					break;
			}

			$brandingImageCSS[] = "max-height: {$this->branding->maxHeight}px;";
			$brandingImageCSS[] = "height: {$this->branding->maxHeight}px;";
			$brandingImageCSS[] = "aspect-ratio: {$this->branding->imageSize->width}/{$this->branding->imageSize->height};";
			$opacity = $this->branding->opacity / 100.0;
			$brandingImageCSS[] = "opacity: {$opacity};";

			$branding = implode("\n", $brandingCSS);
			$brandingImage = implode("\n", $brandingImageCSS);
			$brandingPaused = implode("\n", $brandingPausedCSS);
		}

		$playButtonStyles = "";
		if (!empty($this->options->playButton->enabled)) {
			$playButtonStyles = <<<PLAYBUTTONSTYLES
			    .centered-controls-overlay {
			      display: flex;
			      flex-flow: row nowrap;
			      align-items: center;
			      justify-content: center;
			      align-self: stretch;
			    }
		
			    .centered-controls-overlay media-play-button {
			      margin: 0 64px;
			    }
		
			    *[slot='centered-chrome'] {
			      --media-control-hover-background: none;
			      --media-control-background: none;
			    }
		
			    *[slot='centered-chrome']
			      :is(media-play-button, media-seek-backward-button, media-seek-forward-button) {
			      padding: 0;
			    }
		
			    *[slot='centered-chrome'] media-play-button {
			      --media-button-icon-height: {$this->options->playButton->playButtonHeight}px;
			    }
		
			    *[slot='centered-chrome']
			      :is(media-seek-backward-button, media-seek-forward-button) {
			      --media-button-icon-height: {$this->options->playButton->seekButtonHeight}px;
			    }
PLAYBUTTONSTYLES;

			if ($this->options->playButton->hideWhenPlaying) {
				$playButtonStyles .= <<<HIDEWHENPLAYING
				    media-controller:not([mediapaused]) .centered-controls-overlay {
				      opacity: 0;
				    }
HIDEWHENPLAYING;
			}
		}

		return <<<PLAYERSTYLESBLOCK
		<style>
			:root {
				{$colors}
			}
			
			    
		    media-controller *:where(*:focus) {
		        outline-width: 0 !important;
		        outline-style: none !important;
		    }
		    
		    media-controller[mediaairplayunavailable] media-airplay-button {
		    	display: none;
		    }
		    
            media-controller[mediacastunavailable] media-cast-button {
		    	display: none;
		    }
		    
            media-controller[mediaaudiotrackunavailable] media-audio-track-menu-button {
		    	display: none;
		    }
	
			.no-control-bar-bg {
				--media-control-background: transparent;
				--media-control-hover-background: transparent;
			}
	
			.media-chrome-branding {
				{$branding}
			}
	
			.media-chrome-branding img {
				{$brandingImage}
			}
	
			{$playButtonStyles}
		</style>			
PLAYERSTYLESBLOCK;
	}

	private function settingsMenu($title, $menuTag) {
		return <<<SETTINGSMENUITEM
			<media-settings-menu-item>
		      {$title}
		      <{$menuTag} slot="submenu" hidden>
		        <div slot="title">{$title}</div>
		      </{$menuTag}>
		    </media-settings-menu-item>
SETTINGSMENUITEM;

	}

	/**
	 * @param $videoType
	 * @param $videoUrl
	 * @param string|null $posterImageUrl
	 * @param string|null $filmstripUrl
	 * @param array $videoAttributes
	 * @param string|null $videoId
	 * @param string|null $encodingId
	 * @param string|null $storyboardUrl
	 * @param string[] $subtitles
	 *
	 * @return string
	 */
	public function player($videoType, $videoUrl, $videoWidth, $videoHeight, $posterImageUrl, $filmstripUrl = null, $videoAttributes = [], $videoId = null, $storyboardUrl = null, $subtitles = []) {
		$controlBarsHTML = '';
		$menus = '';

		$hasMenu = [
			'media-rendition-menu-button' => 'media-rendition-menu',
			'media-audio-track-menu-button'=> 'media-audio-track-menu',
			'media-captions-button'=> 'media-captions-menu',
			'media-playback-rate-button'=> 'media-playback-rate-menu',
		];

		foreach($this->controlBars as $controlBar) {
			$settingsButton = arrayFind(function($val) { return $val->tag === 'media-settings-menu-button'; }, $controlBar->controls);
			$hasSettings = !empty($settingsButton) && count($settingsButton->attributes) > 0;

			$controls = [];
			foreach($controlBar->controls as $control) {
				$tag = $control->tag;

				if (!empty($control->menu)) {
					if (!in_array($control->tag, array_keys($hasMenu))) {
						continue;
					}

					$menuName = $hasMenu[$control->tag];
					$menus .= "<{$menuName} hidden anchor='auto'></{$menuName}>";
				}

				if ($tag === 'media-settings-menu-button' && !empty($hasSettings)) {
					$controls[] = "<{$tag}><svg slot='icon' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M12 8.78549C11.3642 8.78549 10.7427 8.97402 10.2141 9.32724C9.68545 9.68046 9.27343 10.1825 9.03013 10.7699C8.78683 11.3573 8.72317 12.0036 8.8472 12.6272C8.97124 13.2507 9.2774 13.8235 9.72696 14.2731C10.1765 14.7227 10.7493 15.0288 11.3729 15.1528C11.9964 15.2769 12.6428 15.2132 13.2302 14.9699C13.8175 14.7266 14.3196 14.3146 14.6728 13.786C15.026 13.2573 15.2146 12.6358 15.2146 12.0001C15.212 11.1483 14.8725 10.3321 14.2702 9.72982C13.6679 9.12752 12.8518 8.78803 12 8.78549ZM18.9402 12.0001C18.9386 12.3004 18.9166 12.6003 18.8743 12.8977L20.8308 14.4295C20.916 14.4998 20.9734 14.5982 20.9928 14.707C21.0122 14.8158 20.9922 14.9279 20.9365 15.0233L19.0857 18.2186C19.0294 18.3132 18.9416 18.3849 18.8377 18.4212C18.7338 18.4574 18.6204 18.4558 18.5175 18.4167L16.2171 17.4925C15.7381 17.8614 15.2139 18.1675 14.6572 18.4035L14.3133 20.8457C14.294 20.9551 14.2374 21.0544 14.153 21.1267C14.0687 21.199 13.9618 21.2397 13.8508 21.2419H10.1492C10.0402 21.2398 9.93509 21.2008 9.85109 21.1312C9.76709 21.0617 9.70915 20.9657 9.68672 20.859L9.34276 18.4167C8.78458 18.1835 8.26 17.8769 7.78289 17.505L5.48247 18.4292C5.37964 18.4683 5.2663 18.47 5.16238 18.4338C5.05847 18.3976 4.97062 18.326 4.9143 18.2315L3.06351 15.0366C3.0078 14.9412 2.98784 14.829 3.0072 14.7202C3.02656 14.6114 3.08398 14.5131 3.16919 14.4427L5.12565 12.911C5.08392 12.6091 5.06191 12.3048 5.05976 12.0001C5.06143 11.6997 5.08344 11.3998 5.12565 11.1024L3.16919 9.57064C3.08398 9.50028 3.02656 9.40192 3.0072 9.29312C2.98784 9.18432 3.0078 9.07219 3.06351 8.97675L4.9143 5.78148C4.97056 5.68689 5.05838 5.61516 5.1623 5.57893C5.26622 5.5427 5.37959 5.54427 5.48247 5.58338L7.78289 6.50757C8.26191 6.1387 8.78612 5.83258 9.34276 5.59664L9.68672 3.15437C9.70597 3.04498 9.76261 2.94566 9.84697 2.8734C9.93133 2.80113 10.0382 2.76041 10.1492 2.75818H13.8508C13.9598 2.7603 14.0649 2.79932 14.1489 2.86887C14.2329 2.93842 14.2909 3.03439 14.3133 3.14111L14.6572 5.58338C15.2161 5.81642 15.7414 6.12304 16.2191 6.49511L18.5175 5.57092C18.6204 5.53178 18.7337 5.53015 18.8376 5.5663C18.9415 5.60246 19.0294 5.6741 19.0857 5.76862L20.9365 8.96389C20.9922 9.05933 21.0122 9.17147 20.9928 9.28026C20.9734 9.38906 20.916 9.48743 20.8308 9.55779L18.8743 11.0895C18.9161 11.3913 18.9381 11.6954 18.9402 12.0001Z'/></svg></{$tag}>";
					continue;
				}

				if (str_starts_with($tag, 'spacer')) {
					$controls[] = '<div style="flex:1; background: var(--media-control-background, var(--media-secondary-color, rgb(20 20 30 / .7)));"></div>';
					continue;
				}

				if ($tag === 'media-playback-rate-button' && !empty($control->menu)) {
					$tag = 'media-playback-rate-menu-button';
				} else if ($tag === 'media-captions-button' && !empty($control->menu)) {
					$tag = 'media-captions-menu-button';
				}

				$attr = implode(' ', $control->attributes);
				$controls[] = "<{$tag} {$attr}></{$tag}>";
			}

			$controlBarClasses = $controlBar->showBg ? '' : 'class="no-control-bar-bg"';
			$controlsHTML = implode("\n", $controls);
			$controlBarsHTML .= <<<CONTROLBARSHTML
				<media-control-bar {$controlBarClasses}>
			      {$controlsHTML}
			    </media-control-bar>
CONTROLBARSHTML;

			if (!empty($hasSettings)) {
				$menuItems = [];
				if (in_array('speed', $settingsButton->attributes)) {
					$menuItems[] = $this->settingsMenu('Speed', 'media-playback-rate-menu');
				}

				if (in_array('quality', $settingsButton->attributes)) {
					$menuItems[] = $this->settingsMenu('Quality', 'media-rendition-menu');
				}

				if (in_array('captions', $settingsButton->attributes)) {
					$menuItems[] = $this->settingsMenu('Captions', 'media-captions-menu');
				}

				if (in_array('audio', $settingsButton->attributes)) {
					$menuItems[] = $this->settingsMenu('Audio', 'media-audio-track-menu');
				}

				if (count($menuItems) > 0) {
					$menuItemsHTML = implode("\n", $menuItems);
					$menus .= <<<SETTINGSMENU
						<media-settings-menu hidden anchor='auto'>
							{$menuItemsHTML}
						</media-settings-menu>
SETTINGSMENU;

				}
			}
		}

		$brandingTop = '';
		$brandingBottom = '';
		if ($this->branding->imageUrl) {
			if (str_contains($this->branding->alignment, 'top-')) {
				$brandingTop = "<media-contral-bar noautohide slot='top-chrome' class='media-chrome-branding'><img src='{$this->branding->imageUrl}'></media-contral-bar>";
			} else {
				$brandingBottom = "<media-contral-bar class='media-chrome-branding'><img src='{$this->branding->imageUrl}'></media-contral-bar>";
			}
		}

		if ($this->options->playButton->enabled) {
			$middleChromeControls = [];
			if ($this->options->playButton->seekBackward) {
				$middleChromeControls[] = '<media-seek-backward-button mediapaused></media-seek-backward-button>';
			}

			$middleChromeControls[] = '<media-play-button mediapaused></media-play-button>';

			if ($this->options->playButton->seekForward) {
				$middleChromeControls[] = '<media-seek-forward-button mediapaused></media-seek-forward-button>';
			}

			$middleChromeControlsHTML = implode("\n", $middleChromeControls);
			$middleChrome = "<div slot='centered-chrome' class='centered-controls-overlay'>{$middleChromeControlsHTML}</div>";
		} else {
			$middleChrome = '<media-loading-indicator slot="centered-chrome" noautohide></media-loading-indicator>';
		}

		$videoTag = 'video';
		$videoSrc = "src='{$videoUrl}'";
		if ($videoType === MediaChromeConfig::VIDEO_TYPE_HLS) {
			$videoTag = 'hls-video';
		} else if ($videoType === MediaChromeConfig::VIDEO_TYPE_MUX) {
			$videoTag = 'mux-video';
			$videoSrc = "playback-id='{$videoUrl}'";
		} else if ($videoType === MediaChromeConfig::VIDEO_TYPE_CLOUDFLARE) {
			$videoTag = 'cloudflare-video';
		}

		if ($videoWidth > 0 && $videoHeight > 0) {
			$videoAspect = " aspect-ratio: {$videoWidth}/{$videoHeight};";
		} else {
			$videoAspect = " aspect-ratio: 1920/1080;";
		}

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

		if (!empty(arrayPath($videoAttributes, 'inline', false))) {
			$tagAttributeList[] = 'playsInline';
		}

		if (!empty(arrayPath($videoAttributes, 'disabled', false))) {
			$tagAttributeList[] = 'disabled';
//			$videoStyles[] = ['pointer-events' => 'none'];
		}

		if (empty(arrayPath($videoAttributes, 'controls', true))) {
			$controlBarsHTML = '';
		}

		if (!empty(arrayPath($videoAttributes, 'preload', null))) {
			$preload = arrayPath($videoAttributes, 'preload', null);
			if ($preload !== null && in_array(strtolower($preload), ['auto', 'metadata', 'none'])) {
				$tagAttributeList[] = "preload='".strtolower($preload)."'";
			}
		}
		$tagAttributes = implode(' ', $tagAttributeList);

		$tracks = [];
		foreach($subtitles as $subtitle) {
			$tracks[] = "<track label='{$subtitle['name']}' srclang='{$subtitle['language_code']}' kind='captions' src='{$subtitle['url']}' />";
		}

		if ($storyboardUrl) {
			$tracks[] = "<track default label='thumbnails' kind='metadata' src='{$storyboardUrl}' />";
		}

		$tracksAttr = implode("\n", $tracks);

		$posterAttr = $posterImageUrl ? "poster='$posterImageUrl'" : '';
		$videoIdAttr = $videoId ? "id='{$videoId}'" : "";
		return <<<PLAYERHTML
			<media-controller {$videoIdAttr} style="width:100%; {$videoAspect}">
				  <{$videoTag}
		            {$videoSrc} {$posterAttr}
		            slot="media"
		            crossorigin
		            {$tagAttributes}
		        >
		        	{$tracksAttr}
		        </{$videoTag}>
		        {$brandingTop}
		        {$middleChrome}
		        {$menus}
		        {$brandingBottom}
				{$controlBarsHTML}
			</media-controller>
PLAYERHTML;
	}
}