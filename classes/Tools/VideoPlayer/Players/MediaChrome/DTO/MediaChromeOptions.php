<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO;

use MediaCloud\Plugin\Model\DTO;

class MediaChromeOptions extends DTO {
	/** @var MediaChromePlayButtonOptions  */
	public $playButton;

	public function __construct($data) {
		$this->playButton = new MediaChromePlayButtonOptions($data['playButton'] ?? [
			'enabled' => true,
			'seekForward' => true,
			'seekBackward' => true,
			'hideWhenPlaying' => true,
			'playButtonHeight' => 96,
			'seekButtonHeight' => 48,
		]);

		parent::__construct($data);
	}
}