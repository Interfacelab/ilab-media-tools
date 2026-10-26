<?php

namespace MediaCloud\Plugin\Model;

class DTO {
	/** @var array */
	private $data = null;

	/**
	 * @param array $data
	 */
	public function __construct($data) {
		$this->data = $data;
	}

	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}

		return null;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}
}