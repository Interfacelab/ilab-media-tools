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

namespace ILAB\MediaCloud\Utilities;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

final class LicensingManager {
	public static function ActivePlan($plan) {
		global $media_cloud_licensing;

		if (strtolower($plan) == 'free') {
			return $media_cloud_licensing->is_free_plan();
		} else {
			return $media_cloud_licensing->is_plan($plan);
		}
	}
}