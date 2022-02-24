<?php


namespace MediaCloud\Plugin\Utilities\Logging\Ray;


/**
 */
class MockRay {
	public function __construct() {
	}

	public function __call($name, $arguments) {
		return $this;
	}
}