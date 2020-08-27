<?php

namespace MediaCloud\Vendor\Aws\FSx;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon FSx** service.
 * @method \MediaCloud\Vendor\Aws\Result cancelDataRepositoryTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelDataRepositoryTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataRepositoryTask(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDataRepositoryTaskAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createFileSystem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFileSystemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createFileSystemFromBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createFileSystemFromBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBackup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBackupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteFileSystem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteFileSystemAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeBackups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBackupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDataRepositoryTasks(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDataRepositoryTasksAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeFileSystems(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeFileSystemsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateFileSystem(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateFileSystemAsync(array $args = [])
 */
class FSxClient extends AwsClient {}
