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

/**
 * Class Config
 * @package MediaCloud\Plugin\Wizard\Config
 *
 */
class Group {
	/** @var string|null  */
	private $introView = null;

	/** @var string|null  */
	private $class = null;

	/** @var Option[]  */
	private $options = [];

	/** @var array|null  */
	private $conditions = null;

	/** @var int */
	private $index = 0;

	public function __construct($groupIndex, $fieldData) {
		$this->index = $groupIndex;
		$this->introView = arrayPath($fieldData, 'introView', null);
		$this->class = arrayPath($fieldData, 'class', null);
		$this->conditions = arrayPath($fieldData, 'conditions', []);

		$options = arrayPath($fieldData, 'options', []);
		foreach($options as $option) {
			$this->options[] = new Option($option);
		}
	}

	/**
	 * @return string|null
	 */
	public function introView() {
		return $this->introView;
	}

	/**
	 * @return string|null
	 */
	public function groupClass() {
		return $this->class;
	}

	/**
	 * @return array|null
	 */
	public function conditions() {
		return $this->conditions;
	}

	/**
	 * @return int
	 */
	public function index() {
		return $this->index;
	}


	/**
	 * @return Option[]
	 */
	public function options() {
		return $this->options;
	}
}