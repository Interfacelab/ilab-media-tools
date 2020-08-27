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
class Config {
	/** @var Section[]  */
	private $sections = [];
	private $initialSection = null;
	private $initialSectionHasSteps = false;

	public function __construct($config) {
		$this->initialSection = arrayPath($config, 'initialSection', null);
		$this->initialSectionHasSteps = arrayPath($config, 'hasSteps', false);
		if (empty($this->initialSection)) {
			throw new \Exception("Missing 'initialSection' in configuration.");
		}

		$sections = arrayPath($config, 'sections', []);
		foreach($sections as $sectionId => $section) {
			$this->sections[$sectionId] = new Section($this, $sectionId, $section);
		}

		if (!isset($this->sections[$this->initialSection])) {
			throw new \Exception("'initialSection' is specified, but the related section is missing in the configuration.");
		}
	}

	/**
	 * @return Section[]
	 */
	public function sections() {
		return $this->sections;
	}

	/**
	 * @param $sectionName
	 *
	 * @return Section|null
	 */
	public function section($sectionName) {
		return (isset($this->sections[$sectionName])) ? $this->sections[$sectionName] : null;
	}

	/**
	 * @return string
	 */
	public function initialSectionName() {
		return $this->initialSection;
	}


	/**
	 * @return bool
	 */
	public function initialSectionHasSteps() {
		return $this->initialSectionHasSteps;
	}

	/**
	 * @param $newInitialSection
	 *
	 * @throws \Exception
	 */
	public function setInitialSectionName($newInitialSection) {
		if (!isset($this->sections[$newInitialSection])) {
			throw new \Exception("Invalid section.");
		}

		$this->initialSection = $newInitialSection;
	}

	/**
	 * @return Section
	 */
	public function initialSection() {
		return $this->sections[$this->initialSection];
	}

	/**
	 * @param $section
	 * @param $step
	 *
	 * @return Step|null
	 */
	public function findStep($section, $step) {
		if (isset($this->sections[$section])) {
			return $this->sections[$section]->step($step);
		}

		return null;
	}
}