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

namespace MediaCloud\Plugin\Wizard;

final class WizardBuilder {
	private $data = [
		'sections' => []
	];

	private $currentSection = null;
	private $currentSectionData = null;

	private $currentStep = null;
	private $currentStepType = null;

	private $currentGroup = null;

	public function __construct($initialSection, $hasSteps = false) {
		$this->data['initialSection'] = $initialSection;
		$this->data['hasSteps'] = $hasSteps;
	}

	public static function instance($initialSection) {
		return new WizardBuilder($initialSection);
	}

	public function section($identifier, $displaySteps = false, $class = null) {
		if (!empty($this->currentSection)) {
			$this->endSection();
		}

		$this->currentSection = $identifier;
		$this->currentSectionData = [
			'displaySteps' => $displaySteps,
			'steps' => [],
			'class' => $class
		];

		return $this;
	}

	public function tutorialSection($identifier, $displaySteps = false) {
		return $this->section($identifier, $displaySteps, 'section-tutorial');
	}

	public function endSection() {
		if (!empty($this->currentStep)) {
			$this->endStep();
		}

		if (!empty($this->currentSection)) {
			$this->data['sections'][$this->currentSection] = $this->currentSectionData;
		}

		$this->currentSection = null;
		$this->currentSectionData = null;

		return $this;
	}

	private function confirmStep($type) {
		if ($this->currentStep == null) {
			throw new \Exception("No step has been started yet.");
		}

		if ($this->currentStepType != $type) {
			throw new \Exception("Invalid step type");
		}
	}

	private function startStep($type, $introView, $title, $description, $next = null, $return = null) {
		if (!empty($this->currentStep)) {
			$this->endStep();
		}

		if ($this->currentSectionData === null) {
			throw new \Exception("Section must be defined before starting a step.");
		}

		$this->currentStep = [
			'type' => $type,
			'introView' => $introView,
			'title' => $title,
			'description' => $description,
			'next' => $next,
			'return' => $return
		];

		$this->currentStepType = $type;
	}

	public function endGroup() {
		if ($this->currentGroup != null) {
			$this->currentStep['groups'][] = $this->currentGroup;
			$this->currentGroup = null;
		}

		return $this;
	}

	public function endStep() {
		if (!empty($this->currentStep)) {
			$this->endGroup();

			if ($this->currentSectionData === null) {
				throw new \Exception("Section must be defined before ending a step.");
			}

			$this->currentSectionData['steps'][] = $this->currentStep;
		}

		$this->currentStepType = null;
		$this->currentStep = null;

		return $this;
	}

	public function intro($introView, $title, $description, $next = null, $return = null) {
		$this->startStep('intro', $introView, $title, $description, $next, $return);
		$this->endStep();

		return $this;
	}

	public function video($videoUrl, $title, $description, $next = null, $return = null) {
		$this->startStep('video', null, $title, $description, $next, $return);
		$this->currentStep['videoUrl'] = $videoUrl;
		$this->endStep();

		return $this;
	}

	public function tutorial($introView, $title, $description, $next = null, $return = null) {
		$this->startStep('tutorial', $introView, $title, $description, $next, $return);
		$this->endStep();

		return $this;
	}

	public function select($title, $description, $class = null) {
		$this->startStep('select', null, $title, $description);
		$this->currentStep['class'] = $class;

		$this->currentStep['groups'] = [];

		return $this;
	}

	public function group($introView, $class = null, $conditions = []) {
		$this->endGroup();

		$this->currentGroup = [
			'introView' => $introView,
			'class' => $class,
			'conditions' => $conditions,
			'options' => []
		];

		return $this;
	}

	public function option($identifier, $title, $descriptionView, $icon, $next, $class = null, $link = null, $target = false) {
		$this->confirmStep('select');

		if ($this->currentGroup == null) {
			throw new \Exception("You must start a group before adding an option.");
		}

		if (!empty($link) && (strpos($link, 'admin:') === 0)) {
			if (media_cloud_licensing()->is_network_active()) {
				$link = network_admin_url(str_replace('admin:', '', $link));
			} else {
				$link = admin_url(str_replace('admin:', '', $link));
			}
		}

		$this->currentGroup['options'][$identifier] = [
			'title' => $title,
			'descriptionView' => $descriptionView,
			'icon' => $icon,
			'next' => $next,
			'class' => $class,
			'link' => $link,
			'target' => $target
		];

		return $this;
	}

	public function form($introView, $title, $description, $handler, $next = null, $return = null) {
		$this->startStep('form', $introView, $title, $description, $next, $return);
		$this->currentStep['fields'] = [];
		$this->currentStep['handler'] = $handler;

		return $this;
	}

	public function field($name, $type, $title, $description, $default, $options = []) {
		$this->confirmStep('form');

		$field = [
			'name' => $name,
			'title' => $title,
			'type' => $type,
			'description' => $description,
			'default' => $default
		];

		if (!empty($options)) {
			$field = array_merge($field, $options);
		}

		$this->currentStep['fields'][] = $field;


		return $this;
	}

	public function textField($name, $title, $description, $default, $required = true) {
		return $this->field($name, 'text-field', $title, $description, $default, ['required' => $required]);
	}

	public function uploadField($name, $title, $description, $required = true) {
		return $this->field($name, 'upload', $title, $description, null, ['required' => $required]);
	}

	public function passwordField($name, $title, $description, $default, $required = true) {
		return $this->field($name, 'password', $title, $description, $default, ['required' => $required]);
	}

	public function checkboxField($name, $title, $description, $default) {
		return $this->field($name, 'checkbox', $title, $description, $default);
	}

	public function selectField($name, $title, $description, $default, $options) {
		return $this->field($name, 'select', $title, $description, $default, [
			'options' => $options
		]);
	}

	public function numberField($name, $title, $description, $default, $min, $max, $step, $required = true) {
		return $this->field('number', $title, $description, $default, [
			'min' => $min,
			'max' => $max,
			'step' => $step,
			'required' => $required
		]);
	}

	public function hiddenField($name, $default) {
		return $this->field($name, 'hidden', null, null, $default);
	}

	public function testStep($introView, $title, $description, $autoStart = false, $next = null, $return = null) {
		$this->startStep('test', $introView, $title, $description, $next, $return);
		$this->currentStep['autoStart'] = $autoStart;
		$this->currentStep['tests'] = [];

		return $this;
	}

	public function test($title, $description, $handler) {
		$this->currentStep['tests'][] = [
			'title' => $title,
			'wizard_ajax' => true,
			'description' => $description,
			'handler' => $handler
		];

		return $this;
	}

	public function task($introView, $title, $description, $handler, $autoStart = false, $next = null, $return = null) {
		$this->startStep('task', $introView, $title, $description, $next, $return);
		$this->currentStep['autoStart'] = $autoStart;
		$this->currentStep['handler'] = $handler;
		$this->endStep();

		return $this;
	}

	public function next($introView, $title, $description, $next = null, $return = null) {
		$this->startStep('next', $introView, $title, $description, $next, $return);
		$this->endStep();

		return $this;
	}

	public function build() {
		$this->endSection();

		return $this->data;
	}
}
