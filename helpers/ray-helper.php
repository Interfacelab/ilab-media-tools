<?php

add_action('plugins_loaded', function() {
	if (!function_exists('\Spatie\WordPressRay\ray')) {
		function ray() {
			static $rayInstance = null;
			if ($rayInstance === null) {
				$rayInstance = new \MediaCloud\Plugin\Utilities\Logging\Ray\MockRay();
			}

			return $rayInstance;
		}
	}
});
