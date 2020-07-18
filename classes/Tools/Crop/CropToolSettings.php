<?php


namespace ILAB\MediaCloud\Tools\Crop;


use ILAB\MediaCloud\Tools\ToolSettings;

/**
 * Class CropToolSettings
 * @package ILAB\MediaCloud\Tools\Crop
 *
 * @property bool $cropQuality
 */
class CropToolSettings extends ToolSettings {
	protected $settingsMap = [
		"cropQuality" => ['mcloud-crop-quality', null, 100],
	];
}