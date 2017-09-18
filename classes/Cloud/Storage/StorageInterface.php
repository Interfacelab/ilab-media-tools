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
	 * Returns a list of buckets
	 * @throws StorageException
	 * @return array
	 */
	public function listBuckets();

	/**
	 * Determines if a bucket exists or not.
	 *
	 * @param $bucket
	 * @throws StorageException
	 * @return bool
	 */
	public function bucketExists($bucket);

	/**
	 * Determines if a file exists in a given bucket.
	 *
	 * @param $bucket
	 * @param $key
	 * @throws StorageException
	 * @return bool
	 */
	public function exists($bucket, $key);

	/**
	 * Copies a file in a given bucket to a new file name.
	 * @param $bucket
	 * @param $sourceKey
	 * @param $destKey
	 * @param $acl
	 * @param bool $mime
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 */
	public function copy($bucket, $sourceKey, $destKey, $acl, $mime=false, $cacheControl=false, $expires=false);

	/**
	 * Uploads a file
	 *
	 * @param $bucket
	 * @param $key
	 * @param $file
	 * @param $acl
	 * @param bool $cacheControl
	 * @param bool $expires
	 * @throws StorageException
	 */
	public function upload($bucket, $key, $file, $acl, $cacheControl=false, $expires=false);

	/**
	 * Deletes a file
	 * @param $bucket
	 * @param $key
	 * @throws StorageException
	 */
	public function delete($bucket, $key);

	/**
	 * Returns info (size, mime type, acl) about an item in a bucket.
	 * @param $bucket
	 * @param $key
	 * @throws StorageException
	 * @return array
	 */
	public function info($bucket, $key);

	/**
	 * Generates a presigned URL for an item in a bucket.
	 *
	 * @param $bucket
	 * @param $key
	 * @throws StorageException
	 * @return string
	 */
	public function presignedUrl($bucket, $key);

	/**
	 * Returns the URL (not-signed) for the item in a bucket
	 * @param $bucket
	 * @param $key
	 * @throws StorageException
	 * @return string
	 */
	public function url($bucket, $key);

	/**
	 * Renders the necessary scripts for handling direct uploads
	 */
	public function renderDirectUploadScripts();
}