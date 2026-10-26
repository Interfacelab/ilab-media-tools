<?php

namespace MediaCloud\Plugin\Tools\VideoPlayer;

class VideoPlayerData {
	public $title = null;
	public $width = 0;
	public $height = 0;
	public $duration = 0;
	public $renditionUrl = null;
	public $originalUrl = null;
	public $playlistUrl = null;
	public $encoderData = null;
	public $encoder = null;
	public $mimeType = null;
	public $extraMetadata = null;
	public $subtitles = null;
	public $filmstripUrl = null;
	public $storyboardUrl = null;

	public function __construct(
		$title,
		$width = 0,
		$height = 0,
		$duration = 0,
		$renditionUrl = null,
		$originalUrl = null,
		$playlistUrl = null,
		$mimeType = null,
		$extraMetadata = null,
		$subtitles = null,
		$filmstripUrl = null,
		$encoderData = null,
		$encoder = null,
		$storyboardUrl = null
	) {
		$this->title = $title;
		$this->width = $width;
		$this->height = $height;
		$this->duration = $duration;
		$this->renditionUrl = $renditionUrl;
		$this->originalUrl = $originalUrl;
		$this->playlistUrl = $playlistUrl;
		$this->mimeType = $mimeType;
		$this->extraMetadata = $extraMetadata;
		$this->subtitles = $subtitles;
		$this->filmstripUrl = $filmstripUrl;
		$this->encoder = $encoder;
		$this->encoderData = $encoderData;
		$this->storyboardUrl = $storyboardUrl;
	}
}