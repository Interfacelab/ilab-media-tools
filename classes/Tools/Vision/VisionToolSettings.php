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

namespace MediaCloud\Plugin\Tools\Vision;

use MediaCloud\Plugin\Tools\MigrationsManager;
use MediaCloud\Plugin\Tools\ToolSettings;
use MediaCloud\Plugin\Utilities\Environment;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class VisionToolSetting
 * @package MediaCloud\Plugin\Tools\Vision
 *
 * @property bool $forceTermCount
 * @property bool $detectLabels
 * @property string $detectLabelsTax
 * @property bool $detectExplicit
 * @property string $detectExplicitTax
 * @property bool $detectCelebrities
 * @property string $detectCelebritiesTax
 * @property bool $detectFaces
 * @property int $detectLabelsConfidence
 * @property int $detectExplicitConfidence
 * @property string[] $ignoredTags
 * @property bool $saveTagsToAlt
 * @property bool $saveTagsToCaption
 * @property bool $saveTagsToDescription
 * @property string $saveTagsPrefix
 */
class VisionToolSettings extends ToolSettings {
	/** @var bool|null  */
    protected $_valid = null;

	/** @var int|null */
	protected $_detectLabelsConfidence = null;

	/** @var int|null */
	protected $_detectExplicitConfidence = null;

	/** @var string[]|null  */
	protected $_ignoredTags = null;

	protected $settingsMap = [
		"forceTermCount" => ['mcloud-vision-force-term-count', null, false],
		"detectLabels" => ['mcloud-vision-detect-labels', null, false],
		"detectLabelsTax" => ['mcloud-vision-detect-labels-tax', null, 'post_tag'],
		"detectExplicit" => ['mcloud-vision-detect-moderation-labels', null, false],
		"detectExplicitTax" => ['mcloud-vision-detect-moderation-labels-tax', null, 'post_tag'],
		"detectFaces" => ['mcloud-vision-detect-faces', null, false],
		"detectCelebrities" => ['mcloud-vision-detect-celebrity', null, false],
		"detectCelebritiesTax" => ['mcloud-vision-detect-celebrity-tax', null, 'post_tag'],
		"saveTagsToAlt" => ['mcloud-vision-save-tags-to-alt', null, false],
		"saveTagsToCaption" => ['mcloud-vision-save-tags-to-caption', null, false],
		"saveTagsToDescription" => ['mcloud-vision-save-tags-to-description', null, false],
		"saveTagsPrefix" => ['mcloud-vision-tax-prefix', null, ''],
	];

	//region Magic
	public function __get($name) {
    	if ($name === 'detectLabelsConfidence') {
    		if ($this->_detectLabelsConfidence === null) {
			    $this->_detectLabelsConfidence = (int)Environment::Option('mcloud-vision-detect-labels-confidence', null, 50);
			    $this->_detectLabelsConfidence = min(100, max(0, $this->_detectLabelsConfidence));
		    }

    		return $this->_detectLabelsConfidence;
	    }

    	if ($name === 'detectExplicitConfidence') {
    		if ($this->_detectExplicitConfidence === null) {
			    $this->_detectExplicitConfidence = (int)Environment::Option('mcloud-vision-detect-moderation-labels-confidence', null, 50);
			    $this->_detectExplicitConfidence = min(100, max(0, $this->_detectExplicitConfidence));
		    }

		    return $this->_detectExplicitConfidence;
	    }

    	if ($name === 'ignoredTags') {
    		if ($this->_ignoredTags === null) {
    			$this->_ignoredTags = [];

			    $toIgnoreString = Environment::Option('mcloud-vision-ignored-tags', '');
			    if (!empty($toIgnoreString)) {
				    $toIgnore = explode(',', $toIgnoreString);
				    foreach($toIgnore as $ignoredTag) {
					    $this->ignoredTags[] = strtolower(trim($ignoredTag));
				    }
			    }
		    }

    		return $this->_ignoredTags;
	    }

		return parent::__get($name);
	}

	public function __isset($name) {
		if (in_array($name, ['detectLabelsConfidence', 'detectExplicitConfidence', 'ignoredTags'])) {
			return true;
		}

		return parent::__isset($name);
	}
	//endregion

	//region Tax Hook
	public function associateTax() {
		if ($this->detectLabels || $this->detectFaces || $this->detectExplicit || $this->detectCelebrities) {
			$taxes = [];

			if ($this->detectLabels && !in_array($this->detectLabelsTax, $taxes)) {
				$taxes[] = $this->detectLabelsTax;
			}

			if ($this->detectExplicit && !in_array($this->detectExplicitTax, $taxes)) {
				$taxes[] = $this->detectExplicitTax;
			}

			if ($this->detectCelebrities && !in_array($this->detectCelebritiesTax, $taxes)) {
				$taxes[] = $this->detectCelebritiesTax;
			}

			add_action( 'init' , function() use ($taxes) {
				foreach($taxes as $tax) {
					if (in_array($tax, ['post_tag', 'category'])) {
						register_taxonomy_for_object_type($tax, 'attachment');
					}
				}
			});
		}
	}
	//endregion
}