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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Backblaze;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class BackblazeSettings
 * @package MediaCloud\Plugin\Tools\Storage\Driver\S3
 *
 * @property string accountId
 * @property string key
 * @property string bucket
 * @property bool settingsError
 *
 */
class BackblazeSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'accountId' => ['mcloud-storage-backblaze-account-id', 'ILAB_BACKBLAZE_ACCOUNT_ID', null],
		'key' => ['mcloud-storage-backblaze-key', 'ILAB_BACKBLAZE_KEY', null],
		'bucket' => ['mcloud-storage-s3-bucket',  ['ILAB_AWS_S3_BUCKET','ILAB_CLOUD_BUCKET'], null],
		'settingsError' => ['ilab-backblaze-settings-error',  null, false],
	];
}