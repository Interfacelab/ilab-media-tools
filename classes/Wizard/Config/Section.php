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
class Section {
	/** @var Config */
	private $config;

	/** @var string */
	private $id;

	/** @var string|null */
	private $class;

	/** @var Step[]  */
	private $steps = [];

	/** @var bool  */
	private $displaySteps = false;

	public function __construct($config, $sectionId, $sectionConfig) {
		$this->config = $config;
		$this->id = $sectionId;

		$this->displaySteps = arrayPath($sectionConfig, 'displaySteps', false);
		$this->class = arrayPath($sectionConfig, 'class', null);

		$steps = arrayPath($sectionConfig, 'steps', []);
		$stepIndex = 0;
		foreach($steps as $stepData) {
			$this->steps[] = new Step($this, $stepIndex, $stepData);
			$stepIndex++;
		}
	}

	/**
	 * @return Config
	 */
	public function config() {
		return $this->config;
	}

	/**
	 * @return string
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	public function sectionClass() {
		return $this->class;
	}

	/**
	 * @return bool
	 */
	public function displaySteps() {
		return $this->displaySteps;
	}

	/**
	 * @return Step[]
	 */
	public function steps() {
		return $this->steps;
	}

	/**
	 * @return int
	 */
	public function stepCount() {
		return count($this->steps);
	}

	/**
	 * @param $stepIndex
	 *
	 * @return Step|null
	 */
	public function step($stepIndex) {
		if ($stepIndex < count($this->steps)) {
			return $this->steps[$stepIndex];
		}

		return null;
	}

	public function stepJson() {
		$data = [];

		$index = 1;
		/** @var Step $step */
		foreach($this->steps as $step) {
			$data[] = [
				'id' => $this->id().'-'.$index,
				'index' => $index,
				'title' => $step->title(),
				'description' => $step->description()
			];

			$index++;
		}

		return json_encode($data, JSON_PRETTY_PRINT);
	}
}