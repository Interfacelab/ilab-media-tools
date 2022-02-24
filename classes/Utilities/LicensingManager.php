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

namespace MediaCloud\Plugin\Utilities;

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

	public static function CanTrack($plan = '') {
		/** @var \Freemius $media_cloud_licensing */
		global $media_cloud_licensing;

		if (empty($plan)) {
			return ($media_cloud_licensing->is_registered() && $media_cloud_licensing->is_tracking_allowed());
		} else {
			return ($media_cloud_licensing->is_plan($plan) && $media_cloud_licensing->is_registered() && $media_cloud_licensing->is_tracking_allowed());
		}
	}

	public static function OptedIn($optInOption, $plan = '') {
		if (!LicensingManager::CanTrack($plan)) {
			return false;
		}

		return Environment::Option($optInOption, null, false);
	}

	public static function ScreenSharingEnabled() {
		if (!LicensingManager::CanTrack()) {
			return false;
		}

		return Environment::Option('mcloud-opt-screen-sharing', null, false);
	}
}