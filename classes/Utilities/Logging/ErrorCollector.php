<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Utilities\Logging;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Simple class for collecting errors
 * @package ILAB\MediaCloud\Utilities\Logging
 */
class ErrorCollector {
    private $errors = [];

    public function __construct() {
    }

    /**
     * Determines if the collector contains any errors
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * The list of errors
     * @return string[]
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Adds an error to the collector
     * @param string $error
     */
    public function addError($error) {
        if (!in_array($error, $this->errors)) {
            $this->errors[] = $error;
        }
    }
}