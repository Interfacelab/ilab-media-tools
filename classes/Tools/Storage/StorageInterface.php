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

namespace MediaCloud\Plugin\Tools\Storage;

use MediaCloud\Plugin\Tools\ToolSettings;
use MediaCloud\Plugin\Utilities\Logging\ErrorCollector;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Interface StorageInterface
 * @package MediaCloud\Plugin\Tools\Storage
 */
interface StorageInterface {
	/**
	 * Determines if the StorageInterface supports direct uploads
	 * @return bool
	 */
	public function supportsDirectUploads();

	/**
	 * Determines if the StorageInterface supports wildcard direct uploads
	 * @return bool
	 */
	public function supportsWildcardDirectUploads();

	/**
	 * The identifier for the storage interface, eg 's3', 'do', etc.
	 * @return string
	 */
	public static function identifier();

	/**
	 * The name of the storage interface, eg 'Amazon S3', etc.
	 * @return mixed
	 */
	public static function name();

	/**
	 * Return the endpoint that the storage interface uses
	 * @return null|string
	 */
	public static function endpoint();

	/**
	 * The default region for this driver
	 * @return null|string
	 */
	public static function defaultRegion();

	/**
	 * If using a custom endpoint, is it a path style endpoint
	 * @return null|bool
	 */
	public static function pathStyleEndpoint();

	/**
	 * Generates a link to the bucket.
	 *
	 * @param $bucket
	 * @return string|null
	 */
	public static function bucketLink($bucket);

	/**
	 * Generates a link to the path
	 *
	 * @param $bucket
	 * @param $key
	 *
	 * @return string|null
	 */
	public function pathLink($bucket, $key);

	/**
	 * Returns true/false if this storage is using signed URLs.
	 *
	 * @param string|null $type
	 *
	 * @return bool
	 */
	public function usesSignedURLs($type = null);

	/**
	 * Returns the expiration for a particular type
	 *
	 * @param null|string $type
	 *
	 * @return int|null
	 */
	public function signedURLExpirationForType($type = null);

	/**
	 * Insures that all the configuration settings are valid and that the storage is enabled.
	 * @return bool
	 */
	public function enabled();

	/**
	 * Flag that indicates if the storage interface has a settings error which prevents it from working.
	 * @return bool
	 */
	public function settingsError();

	/**
	 * Settings for this instance
	 * @return ToolSettings
	 */
	public function settings();

	/**
	 * Validates settings.
	 *
     * @param ErrorCollector|null $errorCollector
	 * @return bool
	 */
	public function validateSettings($errorCollector = null);

    /**
     * Returns the underlying client being used by the driver, eg S3Client or StorageClient
     * @return mixed
     */
	public function client();

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
	 * If using a custom endpoint, is it a path style endpoint
	 * @return bool
	 */
	public function isUsingPathStyleEndPoint();


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
	 * @param string|null $cacheControl
	 * @param string|null $expires
	 * @param string|null $contentType
	 * @param string|null $contentEncoding
	 * @param string|null $contentLength
	 * @throws StorageException
	 * @return string
	 */
	public function upload($key, $fileName, $acl, $cacheControl=null, $expires=null, $contentType=null, $contentEncoding=null, $contentLength=null);

	/**
	 * Creates a directory
	 *
	 * @param $key
	 * @return bool
	 */
	public function createDirectory($key);

	/**
	 * Deletes a directory
	 *
	 * @param $key
	 * @return bool
	 */
	public function deleteDirectory($key);

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
	 * Returns the ACL for the given key
	 * @param string $key
	 * @throws StorageException
	 * @return string|null
	 */
	public function acl($key);

	/**
	 * Insures the ACL is set on the given key.
	 * @param $key
	 * @param $acl
	 * @return mixed
	 */
	public function insureACL($key, $acl);

	/**
	 * Changes the ACL on a given key.
	 * @param $key
	 * @param $acl
	 */
	public function updateACL($key, $acl);

	/**
	 * Determines if this driver can update ACLs
	 *
	 * @return bool
	 */
	public function canUpdateACL();

	/**
	 * Generates a presigned URL for an item in a bucket.
	 *
	 * @param string $key
	 * @throws StorageException
	 * @return string
	 * @param int $expiration
	 * @param array $options
	 */
	public function presignedUrl($key, $expiration = 0, $options = []);

	/**
	 * Returns the URL (not-signed) for the item in a bucket
	 * @param string $key
	 * @param string|null $type
	 * @throws StorageException
	 * @return string
	 */
	public function url($key, $type = null);

	/**
	 * Generates a signed URL for direct uploads
	 * @param string $key
	 * @param string $acl
	 * @param string $mimeType
	 * @param string $cacheControl
	 * @param string $expires
	 *
	 * @return UploadInfo
	 */
	public function uploadUrl($key, $acl, $mimeType=null, $cacheControl = null, $expires = null);

	/**
	 * Enqueue any scripts need for direct uploading.
	 */
	public function enqueueUploaderScripts();

	/**
	 * @param string $path
	 * @param string $delimiter
	 * @param int $limit
	 * @param string $next
	 *
	 * @return StorageFile[]
	 */
	public function dir($path = '', $delimiter = '/', $limit = -1, $next = null);

	/**
	 * Similar to dir() but returns an array of keys as strings.
	 *
	 * @param string $path
	 * @param string $delimiter
	 * @param int $limit
	 * @param string $next
	 * @param bool $recursive
	 *
	 * @return array
	 */
	public function ls($path = '', $delimiter = '/', $limit = -1, $next = null, $recursive = false);

	/**
	 * Determines if the storage provider supports browsing
	 * @return bool
	 */
	public function supportsBrowser();

	/**
	 * Returns the necessary information for optimization service to deliver optimized directly to cloud storage
	 * @return array
	 */
	public function prepareOptimizationInfo();
}
