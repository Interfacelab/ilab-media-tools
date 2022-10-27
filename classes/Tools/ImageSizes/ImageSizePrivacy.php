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

namespace MediaCloud\Plugin\Tools\ImageSizes;

use MediaCloud\Plugin\Tools\Tool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\NoticeManager;
use MediaCloud\Plugin\Utilities\Tracker;
use MediaCloud\Plugin\Utilities\View;
use function MediaCloud\Plugin\Utilities\gen_uuid;
use function MediaCloud\Plugin\Utilities\json_response;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class ImageSizePrivacy {
	private static $imageSizePrivacy = null;

	private static function insurePrivacy() {
		if (static::$imageSizePrivacy === null) {
			static::$imageSizePrivacy = get_option('mcloud-image-privacy', []);
		}
	}


	public static function privacyForSize($size, $default = 'inherit') {
		static::insurePrivacy();

		$privacy = isset(static::$imageSizePrivacy[$size]) ? static::$imageSizePrivacy[$size] : $default;
		if ($privacy != 'inherit') {
			return $privacy;
		}

		return $default;
	}

	public static function updatePrivacyForSize($size, $privacy) {
		static::insurePrivacy();

		static::$imageSizePrivacy[$size] = $privacy;
		update_option('mcloud-image-privacy', static::$imageSizePrivacy);
	}

}