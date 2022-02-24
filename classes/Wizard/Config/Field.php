<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Wizard\Config;

use function MediaCloud\Plugin\Utilities\arrayPath;
use MediaCloud\Plugin\Utilities\Environment;

/**
 * Class Config
 * @package MediaCloud\Plugin\Wizard\Config
 *
 */
class Field {
	/** @var string|null  */
	private $name;

	/** @var string|null  */
	private $title;

	/** @var string|null  */
	private $type;

	/** @var string|null  */
	private $description;

	/** @var mixed|null  */
	private $default;

	/** @var string[string]  */
	private $options = [];

	/** @var int  */
	private $min = 0;

	/** @var int  */
	private $max = 0;

	/** @var int  */
	private $step = 0;

	/** @var bool  */
	private $required = true;

	public function __construct($fieldData) {
		$this->name = arrayPath($fieldData, 'name', null);
		if (empty($this->name)) {
			throw new \Exception("Missing name for field.");
		}

		$this->type = arrayPath($fieldData, 'type', null);
		if (empty($this->type)) {
			throw new \Exception("Missing type for field.");
		}

		$this->title = arrayPath($fieldData, 'title', null);
		$this->description = arrayPath($fieldData, 'description', null);

		$default = arrayPath($fieldData, 'default', null);
		if ($this->type == 'hidden') {
			$this->default = $default;
		} else {
			$this->default = Environment::Option($this->name, null, $default);
		}

		$this->options = arrayPath($fieldData, 'options', []);

		$this->min = arrayPath($fieldData, 'min', 0);
		$this->max = arrayPath($fieldData, 'max', 0);
		$this->step = arrayPath($fieldData, 'step', 0);
		$this->required = arrayPath($fieldData, 'required', true);
	}

	/**
	 * @return string
	 */
	function name() {
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	function title() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	function type() {
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	function description() {
		return $this->description;
	}

	/**
	 * @return mixed|null
	 */
	function defaultValue() {
		return $this->default;
	}

	/**
	 * @return string[string]
	 */
	function options() {
		return $this->options;
	}

	/**
	 * @return int
	 */
	function min() {
		return $this->min;
	}

	/**
	 * @return int
	 */
	function max() {
		return $this->max;
	}

	/**
	 * @return int
	 */
	function step() {
		return $this->step;
	}

	/**
	 * @return bool
	 */
	function required() {
		return $this->required;
	}
}