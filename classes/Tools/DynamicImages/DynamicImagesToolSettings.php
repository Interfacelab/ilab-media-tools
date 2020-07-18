<?php


namespace ILAB\MediaCloud\Tools\DynamicImages;


use ILAB\MediaCloud\Tools\ToolSettings;

/**
 * Class DynamicImagesToolSettings
 * @package ILAB\MediaCloud\Tools\DynamicImages
 *
 * @property bool $keepThumbnails
 */
class DynamicImagesToolSettings extends ToolSettings {
	protected $settingsMap = [
		'keepThumbnails' => ['mcloud-imgix-generate-thumbnails', null, true],
	];
}