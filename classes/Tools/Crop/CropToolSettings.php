<?php


namespace MediaCloud\Plugin\Tools\Crop;


use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class CropToolSettings
 * @package MediaCloud\Plugin\Tools\Crop
 *
 * @property bool $cropQuality
 */
class CropToolSettings extends ToolSettings {
	protected $settingsMap = [
		"cropQuality" => ['mcloud-crop-quality', null, 100],
	];
}