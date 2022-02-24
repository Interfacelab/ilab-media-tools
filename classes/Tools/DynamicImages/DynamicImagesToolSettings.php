<?php


namespace MediaCloud\Plugin\Tools\DynamicImages;


use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class DynamicImagesToolSettings
 * @package MediaCloud\Plugin\Tools\DynamicImages
 *
 * @property bool $keepThumbnails
 * @property bool $servePrivateImages
 */
class DynamicImagesToolSettings extends ToolSettings {
	protected $settingsMap = [
		'keepThumbnails' => ['mcloud-imgix-generate-thumbnails', null, true],
		'servePrivateImages' => ['mcloud-imgix-serve-private-images', null, true],
	];
}