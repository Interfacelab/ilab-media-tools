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

namespace ILAB\MediaCloud\Cloud\Storage;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Interface StorageInterface
 * @package ILAB\MediaCloud\Cloud\Storage
 */
interface StorageInterface {
	/**
	 * Determines if the StorageInterface supports direct uploads
	 * @return bool
	 */
	public function supportsDirectUploads();

	/**
	 * Insures that all the configuration settings are valid and that the storage is enabled.
	 * @return bool
	 */
	public function enabled();

	/**
	 * Validates settings.
	 *
	 * @return bool
	 */
	public function validateSettings();

	/**
	 * Returns the name of the bucket this storage is using.
	 *
	 * @return string
	 */
	public function bucket();

	/**
	 * Determines if a file exists in a given bucket.
	 *
	 * @param $key
	 * @throws StorageException
	 * @return bool
	 */
	public function exists($key);

	/**
	 * Copies a file in a given bucket to a new file name.
	 * @param $sourceKey
	 * @param $destKey
	 * @param $acl
	 * @param bool $mime
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 */
	public function copy($sourceKey, $destKey, $acl, $mime=false, $cacheControl=false, $expires=false);

	/**
	 * Uploads a file, returning the new URL for the file.
	 *
	 * @param $key
	 * @param $file
	 * @param $acl
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 * @return string
	 */
	public function upload($key, $file, $acl, $cacheControl=false, $expires=false);

	/**
	 * Deletes a file
	 * @param $key
	 * @throws StorageException
	 */
	public function delete($key);

	/**
	 * Returns info (size, mime type, acl) about an item in a bucket.
	 * @param $key
	 * @throws StorageException
	 * @return array
	 */
	public function info($key);

	/**
	 * Generates a presigned URL for an item in a bucket.
	 *
	 * @param $key
	 * @throws StorageException
	 * @return string
	 */
	public function presignedUrl($key);

	/**
	 * Returns the URL (not-signed) for the item in a bucket
	 * @param $key
	 * @throws StorageException
	 * @return string
	 */
	public function url($key);

	/**
	 * Generates a signed URL for direct uploads
	 * @param string $key
	 * @param string $acl
	 * @param string $cacheControl
	 * @param string $expires
	 *
	 * @return array|null
	 */
	public function uploadUrl($key, $acl, $cacheControl = null, $expires = null);


	/**
	 * Renders the necessary scripts for handling direct uploads
	 */
	public function renderDirectUploadScripts();
}