<?php


namespace MediaCloud\Plugin\Utilities\Logging\Ray;

use Spatie\WordPressRay\Spatie\Ray\Ray;

final class RayMacros {
	private static $_initialized = false;
	static function init() {
		if (self::$_initialized) {
			return;
		}

		self::$_initialized = true;


		Ray::macro('mediacloud', function(string $level, string $html) {
			$payload = new RayPayload($level, $html, $this->settings->path_map);
			return $this->sendRequest($payload);
		});


	}
}