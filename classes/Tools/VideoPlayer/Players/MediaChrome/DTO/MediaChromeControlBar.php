<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO;

use MediaCloud\Plugin\Model\DTO;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\gen_uuid;

/**
 * @property bool showBg
 */
class MediaChromeControlBar extends DTO {
	/**
	 * @var MediaChromeControl[]
	 */
	public $controls;

	public function __construct($data) {
		$defaultControls = [
			['id' => gen_uuid(), 'tag' => 'media-play-button', 'label' => 'Play Button', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-seek-backward-button', 'label' => 'Seek Backward', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-seek-forward-button', 'label' => 'Seek Forward', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-mute-button', 'label' => 'Mute Button', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-volume-range', 'label' => 'Volume Range', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-time-range', 'label' => 'Time Range', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-time-display', 'label' => 'Time Display', 'menu' => false, 'attributes' => [ 'showduration' ]],
			['id' => gen_uuid(), 'tag' => 'media-pip-button', 'label' => 'Picture in Picture Button', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-airplay-button', 'label' => 'Airplay', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-fullscreen-button', 'label' => 'Fullscreen Button', 'menu' => false, 'attributes' => []],
			['id' => gen_uuid(), 'tag' => 'media-settings-menu-button', 'label' => 'Settings Menu Button', 'menu' => false, 'attributes' => ['speed', 'quality', 'captions', 'audio']],
		];

		$controls = arrayPath($data, 'controls', $defaultControls);
		foreach($controls as $control) {
			$this->controls[] = new MediaChromeControl($control);
		}
		
		parent::__construct($data);
	}
}