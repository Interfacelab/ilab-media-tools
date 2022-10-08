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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Cloudflare;

use MediaCloud\Plugin\Tools\Storage\Driver\S3\S3StorageSettings;

/**
 * Class SupabaseStorageSettings
 *
 * @property string publicBucketUrl
 * @property bool settingsError
 *
 */
class CloudflareStorageSettings extends S3StorageSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $cloudflareSettingsMap = [
		'publicBucketUrl' => ['mcloud-storage-cloudflare-r2-public-url',  null, false],
		'settingsError' => ['mcloud-storage-cloudflare-settings-error',  null, false],
	];

	public function __construct($storage) {
		$this->settingsMap = array_merge($this->settingsMap, $this->cloudflareSettingsMap);
		parent::__construct($storage);
	}
}