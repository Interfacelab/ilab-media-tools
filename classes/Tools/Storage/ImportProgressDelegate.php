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

namespace ILAB\MediaCloud\Tools\Storage;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

interface ImportProgressDelegate {
	/**
	 * Update the current progress
	 *
	 * @param $newIndex
	 */
	public function updateCurrentIndex($newIndex);

	/**
	 * Update the name of the currently processed file
	 * @param $newFile
	 */
	public function updateCurrentFileName($newFile);

}