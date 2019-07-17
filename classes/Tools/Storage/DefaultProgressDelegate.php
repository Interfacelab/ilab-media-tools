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

class DefaultProgressDelegate implements ImportProgressDelegate {
	#region ImportProgressDelegate
	public function updateCurrentIndex($newIndex) {
		update_option('ilab_s3_import_current', $newIndex);
	}

	public function updateCurrentFileName($newFile) {
		update_option('ilab_s3_import_current_file', $newFile);
	}
	#endregion
}