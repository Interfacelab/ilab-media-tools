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

namespace ILAB\MediaCloud\Vision;

use ILAB\MediaCloud\Tools\MigrationsManager;
use ILAB\MediaCloud\Utilities\Environment;

if (!defined('ABSPATH')) { header('Location: /'); die; }

class VisionConfig {
    protected $valid = false;

    /** @var bool */
    protected $detectLabels = false;

    /** @var string|null */
    protected $detectLabelsTax = 'post_tag';

    /** @var int */
    protected $detectLabelsConfidence = 50;

    /** @var bool */
    protected $detectExplicit = false;

    /** @var bool */
    protected $detectExplicitTax = 'post_tag';

    /** @var int */
    protected $detectExplicitConfidence = 50;

    /** @var bool */
    protected $detectCelebrities = false;

    /** @var string|null */
    protected $detectCelebritiesTax = 'post_tag';

    /** @var bool */
    protected $detectFaces = false;

    /** @var array */
    protected $ignoredTags = [];

    //region Constructor
    public function __construct() {
        $this->valid = !MigrationsManager::instance()->hasDeprecatedEnvironment('vision');

        $this->detectLabels = Environment::Option('mcloud-vision-detect-labels', null, false);
        $this->detectLabelsTax = Environment::Option('mcloud-vision-detect-labels-tax', null, 'post_tag');
        $this->detectLabelsConfidence = (int)Environment::Option('mcloud-vision-detect-labels-confidence', null, 50);
        $this->detectExplicit = Environment::Option('mcloud-vision-detect-moderation-labels', null, false);
        $this->detectExplicitTax = Environment::Option('mcloud-vision-detect-moderation-labels-tax', null, 'post_tag');
        $this->detectExplicitConfidence = (int)Environment::Option('mcloud-vision-detect-moderation-labels-confidence', null, 50);
        $this->detectCelebrities = Environment::Option('mcloud-vision-detect-celebrity', null, false);
        $this->detectCelebritiesTax = Environment::Option('mcloud-vision-detect-celebrity-tax', null, 'post_tag');
        $this->detectFaces = Environment::Option('mcloud-vision-detect-faces', null, false);

        $this->detectLabelsConfidence = min(100, max(0, $this->detectLabelsConfidence));
        $this->detectExplicitConfidence = min(100, max(0, $this->detectExplicitConfidence));

        $toIgnoreString = Environment::Option('mcloud-vision-ignored-tags', '');
        if (!empty($toIgnoreString)) {
            $toIgnore = explode(',', $toIgnoreString);
            foreach($toIgnore as $ignoredTag) {
                $this->ignoredTags[] = strtolower(trim($ignoredTag));
            }
        }

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

    //region Properties

    public function valid() {
        return $this->valid;
    }

    public function detectLabels() {
        return $this->detectLabels;
    }

    public function detectLabelsTax(){
        return $this->detectLabelsTax;
    }

    public function detectLabelsConfidence() {
        return $this->detectLabelsConfidence;
    }

    public function detectExplicit() {
        return $this->detectExplicit;
    }

    public function detectExplicitTax() {
        return $this->detectExplicitTax;
    }

    public function detectExplicitConfidence() {
        return $this->detectExplicitConfidence;
    }

    public function detectCelebrities() {
        return $this->detectCelebrities;
    }

    public function detectCelebritiesTax() {
        return $this->detectCelebritiesTax;
    }

    public function detectFaces() {
        return $this->detectFaces;
    }

    public function ignoredTags() {
        return $this->ignoredTags;
    }

    //endregion
}