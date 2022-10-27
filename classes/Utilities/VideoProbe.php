<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Utilities;

/**
 * Class for getting metadata about remote or local video files.
 */
class VideoProbe {
	/** @var VideoProbe  */
	private static $instance = null;

	/** @var bool  */
	private $enabled = false;

	/** @var string|null  */
	private $ffprobe = null;

	/** @var null  */
	private $error = null;

	public function __construct() {
		$this->enabled = false;

		$disabled_functions=explode(',',ini_get('disable_functions'));
		$execEnabled = function_exists('shell_exec') && is_callable('shell_exec') && !in_array('shell_exec', $disabled_functions);
		if ($execEnabled) {
			$this->ffprobe = Environment::Option('mcloud-direct-uploads-ffprobe-path', null, null);
			if (empty($this->ffprobe)) {
				$this->ffprobe = trim(shell_exec('which ffprobe'));
			}

			if (!empty($this->ffprobe)) {
				if (@file_exists($this->ffprobe)) {
					$this->enabled = is_executable($this->ffprobe);
					if (!$this->enabled) {
						$this->error = 'FFProbe is installed but is not marked as executable.';
					}
				} else {
					$this->error = 'FFProbe is installed, but is inaccessible due to security (likely due to <a href="https://www.php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank"><code>open_basedir</code></a> settings).';
				}
			} else {
				$this->error = "FFProbe is not installed.";
			}
		} else {
			$this->error = "The <code>shell_exec</code> PHP function is disabled.";
		}
	}

	/**
	 * Returns the static instance of this class
	 * @return VideoProbe
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			$class = self::class;
			self::$instance = new $class();
		}

		return self::$instance;
	}

	private function timecode($duration) {
		$hours = floor($duration / (60.0 * 60.0));
		$duration -= ($hours * 60 * 60);

		$minutes = floor($duration / 60.0);
		$duration -= ($minutes * 60);

		$seconds = round($duration);

		if ($hours > 0) {
			return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
		} else {
			return sprintf('%02d:%02d', $minutes, $seconds);
		}
	}

	private function parseFPS($framerate) {
		if (empty($framerate)) {
			return null;
		}

		$parts = explode('/', $framerate);
		return intval($parts[0]);
	}

	public function enabled() {
		return $this->enabled;
	}

	public function error() {
		return $this->error;
	}

	public function probe($file) {
		if (!$this->enabled) {
			return [];
		}

		setlocale(LC_CTYPE, 'en_US.UTF-8');
		$command = sprintf("%s -i %s -loglevel quiet -show_format -show_streams -print_format json", $this->ffprobe, escapeshellarg($file));
		$json = shell_exec($command);
		if (empty($json)) {
			return [];
		}

		$data = json_decode($json, true);
		if (!isset($data['format'])) {
			return [];
		}

		$videoStream = null;
		$audioStream = null;

		foreach($data['streams'] as $stream) {
			$codecType = arrayPath($stream, 'codec_type', null);
			if (($codecType == 'video') && empty($videoStream)) {
				$videoStream = $stream;
			} else if (($codecType == 'audio') && empty($audioStream)) {
				$audioStream = $stream;
			}
		}

		if (empty($videoStream)) {
			return [];
		}

		$extension = pathinfo(basename($file), PATHINFO_EXTENSION);
		if (strpos($extension, '?') !== false) {
			$parts = explode('?', $extension);
			$extension = $parts[0];
		}

		$result = [
			'width' => intval(arrayPath($videoStream, 'width', 0)),
			'height' => intval(arrayPath($videoStream, 'height', 0)),
			'length' => intval(round(floatval(arrayPath($data,'format/duration')))),
			'length_formatted' => $this->timecode(floatval(arrayPath($data,'format/duration'))),
			'dataformat' => arrayPath($data, 'format/format_long_name', "unknown"),
			'fileformat' => strtolower($extension),
			'mime_type' => 'video/'.strtolower($extension),
			'video' => [
				'codec' => arrayPath($videoStream, 'codec_name', null),
				'profile' => arrayPath($videoStream, 'profile', null),
				'level' => arrayPath($videoStream, 'level', null),
				'format' => arrayPath($videoStream, 'pix_fmt', null),
				'frames' => intval(arrayPath($videoStream, 'nb_frames', 0)),
				'fps' => $this->parseFPS(arrayPath($videoStream, 'avg_frame_rate', null)),
			],
		];

		if (!empty($audioStream)) {
			$result['audio'] = [
				'codec' => arrayPath($audioStream, 'codec_long_name', null),
				'sample_rate' => intval(arrayPath($audioStream, 'sample_rate', 0)),
				'channels' => arrayPath($audioStream, 'channels', 0),
				'bits_per_sample' => arrayPath($audioStream, 'bits_per_sample', 0),
				'channelmode' => arrayPath($audioStream, 'channel_layout', null),
				'dataformat' => arrayPath($audioStream, 'codec_tag_string', null)
			];
		}

		return $result;

	}


}