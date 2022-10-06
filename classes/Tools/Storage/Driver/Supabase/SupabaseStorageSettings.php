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

namespace MediaCloud\Plugin\Tools\Storage\Driver\Supabase;

use MediaCloud\Plugin\Tools\ToolSettings;

/**
 * Class SupabaseStorageSettings
 *
 * @property string storageUrl
 * @property string key
 * @property string bucket
 * @property bool settingsError
 *
 */
class SupabaseStorageSettings extends ToolSettings {
	/**
	 * Map of property names to setting names
	 * @var string[]
	 */
	protected $settingsMap = [
		'storageUrl' => ['mcloud-storage-supabase-url', 'MCLOUD_SUPABASE_URL', null],
		'key' => ['mcloud-storage-supabase-key', 'MCLOUD_SUPABASE_KEY', null],
		'bucket' => ['mcloud-storage-supabase-bucket', 'MCLOUD_SUPABASE_BUCKET', null],
		'settingsError' => ['mcloud-storage-supabase-settings-error',  null, false],
	];
}