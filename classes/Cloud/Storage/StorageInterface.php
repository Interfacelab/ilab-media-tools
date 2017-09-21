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
	 * Returns the name of the region this storage is using.
	 *
	 * @return string|null
	 */
	public function region();

	/**
	 * Determines if a file exists in a given bucket.
	 *
	 * @param string $key
	 * @throws StorageException
	 * @return bool
	 */
	public function exists($key);

	/**
	 * Copies a file in a given bucket to a new file name.
	 * @param string $sourceKey
	 * @param string $destKey
	 * @param string $acl
	 * @param bool $mime
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 */
	public function copy($sourceKey, $destKey, $acl, $mime=false, $cacheControl=false, $expires=false);

	/**
	 * Uploads a file, returning the new URL for the file.
	 *
	 * @param string $key
	 * @param string $fileName
	 * @param string $acl
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 * @return string
	 */
	public function upload($key, $fileName, $acl, $cacheControl=false, $expires=false);

	/**
	 * Deletes a file
	 * @param string $key
	 * @throws StorageException
	 */
	public function delete($key);

	/**
	 * Returns info (size, mime type, acl) about an item in a bucket.
	 * @param string $key
	 * @throws StorageException
	 * @return FileInfo
	 */
	public function info($key);

	/**
	 * Generates a presigned URL for an item in a bucket.
	 *
	 * @param string $key
	 * @throws StorageException
	 * @return string
	 */
	public function presignedUrl($key);

	/**
	 * Returns the URL (not-signed) for the item in a bucket
	 * @param string $key
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
	 * @return UploadInfo
	 */
	public function uploadUrl($key, $acl, $cacheControl = null, $expires = null);
}