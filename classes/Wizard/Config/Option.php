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
class Option {
	/** @var string|null  */
	private $title = null;

	/** @var string|null  */
	private $descriptionView = null;

	/** @var string|null  */
	private $icon = null;

	/** @var string|null  */
	private $next = null;

	/** @var string|null  */
	private $class = null;

	/** @var string|null  */
	private $link = null;

	/** @var string|null  */
	private $target = null;


	public function __construct($fieldData) {
		$this->title = arrayPath($fieldData, 'title', null);
		$this->icon = arrayPath($fieldData, 'icon', null);
		$this->next = arrayPath($fieldData, 'next', null);
		$this->class = arrayPath($fieldData, 'class', null);
		$this->link = arrayPath($fieldData, 'link', null);
		$this->target = arrayPath($fieldData, 'target', null);
		$this->descriptionView = arrayPath($fieldData, 'descriptionView', null);
	}

	/**
	 * @return string|null
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * @return string|null
	 */
	public function descriptionView() {
		return $this->descriptionView;
	}

	/**
	 * @return string|null
	 */
	public function icon() {
		return $this->icon;
	}

	/**
	 * @return string|null
	 */
	public function next() {
		return $this->next;
	}

	/**
	 * @return string|null
	 */
	public function optionClass() {
		return $this->class;
	}

	/**
	 * @return string|null
	 */
	public function link() {
		return $this->link;
	}

	/**
	 * @return string|null
	 */
	public function target() {
		return $this->target;
	}
}