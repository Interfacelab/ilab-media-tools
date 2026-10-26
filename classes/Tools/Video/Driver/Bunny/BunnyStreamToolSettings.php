<?php

namespace MediaCloud\Plugin\Tools\Video\Driver\Bunny;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * @property string|null $libraryId
 * @property string|null $apiKey
 * @property string|null $cdnUrl
 * @property bool $deleteFromStream
 * @property bool $processUploads
 */
class BunnyStreamToolSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'libraryId' => ['media-cloud-bunny-stream-library-id', null, null],
		'apiKey' => ['media-cloud-bunny-stream-api-key', null, null],
		'cdnUrl' => ['media-cloud-bunny-stream-cdn-url', null, null],
		'processUploads' => ['media-cloud-bunny-stream-process-uploads', null, true],
		'deleteFromStream' => ['media-cloud-bunny-stream-delete-from-stream', null, true],
	];

}