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

if (!defined('ABSPATH')) { header('Location: /'); die; }

return [
	's3' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\S3Storage::class,
	'imgix' => \MediaCloud\Plugin\Tools\Imgix\ImgixTool::class,
	'google' => \MediaCloud\Plugin\Tools\Storage\Driver\GoogleCloud\GoogleStorage::class,
	'other-s3' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\OtherS3Storage::class,
	'do' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\DigitalOceanStorage::class,
	'wasabi' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\WasabiStorage::class,
	'minio' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\MinioStorage::class,
	'backblaze-s3' => \MediaCloud\Plugin\Tools\Storage\Driver\S3\BackblazeS3Storage::class,
];