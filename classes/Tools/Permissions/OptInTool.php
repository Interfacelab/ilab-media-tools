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

namespace MediaCloud\Plugin\Tools\Permissions;

use MediaCloud\Plugin\Tools\Tool;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class OptInTool
 *
 * Tool for opt in permissions.
 */
class OptInTool extends Tool {

	//region Tool Overrides

	public function enabled() {
		return true;
	}

	public function envEnabled() {
		return true;
	}

	public function alwaysEnabled() {
		return true;
	}

	//endregion
}
