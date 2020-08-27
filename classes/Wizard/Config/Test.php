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
class Test {
	/** @var string|null */
	private $id;

	/** @var string|null  */
	private $title;

	/** @var string|null  */
	private $description;

	/** @var callable|null  */
	private $handler = null;

	/**
	 * Test constructor.
	 *
	 * @param Section $section
	 * @param int $stepIndex
	 * @param int $testIndex
	 * @param array $testData
	 */
	public function __construct($section, $stepIndex, $testIndex, $testData) {
		$this->id = $section->id().'-'.$stepIndex.'-'.$testIndex;

		$this->title = arrayPath($testData, 'title', null);
		$this->description = arrayPath($testData, 'description', null);
		$this->handler = arrayPath($testData, 'handler', null);

		if (!empty($this->handler) && is_callable($this->handler)) {
			add_action('wp_ajax_'.$this->id, $this->handler);
		}
	}

	/**
	 * @return string
	 */
	function id() {
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	function title() {
		return $this->title;
	}

	/**
	 * @return string|null
	 */
	function description() {
		return $this->description;
	}

	/**
	 * @return callable|null
	 */
	function handler() {
		return $this->handler;
	}
}