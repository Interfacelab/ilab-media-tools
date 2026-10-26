<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer\Players\MediaChrome\DTO;

use MediaCloud\Plugin\Model\DTO;

/**
 * @property int|null  imageId
 * @property string|null imageUrl
 * @property int padding
 * @property int maxHeight
 * @property int opacity
 * @property string alignment
 * @property bool autoHide
 */
class MediaChromeBranding extends DTO {
	public $imageSize;

	public function __construct($data) {
		$this->imageSize = new MediaChromeImageSize($data['imageSize'] ?? ['width' => 0, 'height' => 0]);

		parent::__construct($data);
	}
}