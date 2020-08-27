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

use Acme\Tester;
use function MediaCloud\Plugin\Utilities\arrayPath;

/**
 * Class Config
 * @package MediaCloud\Plugin\Wizard\Config
 *
 */
class Step {
	/** @var string  */
	private $id;

	/** @var Section  */
	private $section;

	/** @var string  */
	private $type;

	/** @var string|null  */
	private $introView;

	/** @var string|null  */
	private $successView;

	/** @var string|null  */
	private $errorView;

	/** @var string|null  */
	private $title;

	/** @var string|null  */
	private $description;

	/** @var Group[] */
	private $groups = [];

	/** @var Field[]  */
	private $fields = [];

	/** @var callable|null  */
	private $handler = null;

	/** @var string|null  */
	private $next = null;

	/** @var string|null  */
	private $class = null;

	/** @var string|null  */
	private $return = null;

	/** @var string|null  */
	private $videoUrl = null;

	/** @var bool  */
	private $autoStart = false;

	/** @var Test[]  */
	private $tests = [];

	/**
	 * Step constructor.
	 *
	 * @param Section $section
	 * @param int $stepIndex
	 * @param array $stepData
	 *
	 * @throws \Exception
	 */
	public function __construct($section, $stepIndex, $stepData) {
		$this->id = $section->id().'-'.$stepIndex;

		$this->section = $section;
		$this->type = arrayPath($stepData, 'type', null);
		if (empty($this->type)) {
			throw new \Exception("Missing type specifier for step.");
		}

		$this->introView = arrayPath($stepData, 'introView', null);
		$this->title = arrayPath($stepData, 'title', null);
		$this->description = arrayPath($stepData, 'description', null);

		$groups = arrayPath($stepData, 'groups', []);
		$groupIndex = 0;
		foreach($groups as $groupData) {
			$this->groups[] = new Group($groupIndex, $groupData);
			$groupIndex++;
		}


		$fields = arrayPath($stepData, 'fields', []);
		foreach($fields as $field) {
			$this->fields[] = new Field($field);
		}

		$this->handler = arrayPath($stepData, 'handler', null);
		$this->next = arrayPath($stepData, 'next', null);
		$this->class = arrayPath($stepData, 'class', null);
		$this->return = arrayPath($stepData, 'return', null);
		$this->successView = arrayPath($stepData, 'successView', null);
		$this->errorView = arrayPath($stepData, 'errorView', null);
		$this->videoUrl = arrayPath($stepData, 'videoUrl', null);
		$this->autoStart = arrayPath($stepData, 'autoStart', null);

		$tests = arrayPath($stepData, 'tests', []);
		$testIndex = 0;
		foreach($tests as $testData) {
			$this->tests[] = new Test($section, $stepIndex, $testIndex, $testData);
			$testIndex++;
		}

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
	 * @return Section
	 */
	function section() {
		return $this->section;
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
	function stepClass() {
		return $this->class;
	}

	/**
	 * @return string|null
	 */
	function introView() {
		return $this->introView;
	}

	/**
	 * @return string|null
	 */
	function successView() {
		return $this->successView;
	}

	/**
	 * @return string|null
	 */
	function errorView() {
		return $this->errorView;
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
	 * @return Group[]
	 */
	function groups() {
		return $this->groups;
	}

	/**
	 * @return Field[]
	 */
	function fields() {
		return $this->fields;
	}

	/**
	 * @return callable|null
	 */
	function handler() {
		return $this->handler;
	}

	/**
	 * @return string|null
	 */
	function next() {
		return $this->next;
	}

	/**
	 * @return string|null
	 */
	function returnLink() {
		return $this->return;
	}

	/**
	 * @return bool
	 */
	function autoStart() {
		return $this->autoStart;
	}

	/**
	 * @return string|null
	 */
	function videoUrl() {
		return $this->videoUrl;
	}

	public function testsJson() {
		$data = [];

		$index = 1;
		/** @var Test $test */
		foreach($this->tests as $test) {
			$data[] = [
				'action' => $test->id(),
				'index' => $index,
				'wizard_ajax' => true,
				'title' => $test->title(),
				'nonce' => wp_create_nonce('media-cloud-wizard-test'),
				'description' => $test->description()
			];

			$index++;
		}

		return json_encode($data, JSON_PRETTY_PRINT);
	}
}
