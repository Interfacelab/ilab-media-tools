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

use ILAB\MediaCloud\Wizard\WizardBuilder;
use ILAB\MediaCloud\Storage\Driver\S3\S3Storage;

$builder = WizardBuilder::instance('intro');
$builder
	->section('intro')
		->intro('wizard.intros.intro', null, null, 'cloud-storage')
	->endSection()
	->section('cloud-storage')
		->select(null, null )
			->group('wizard.cloud-storage.intro', 'select-icons')
				->option('s3', 'Amazon S3', 'wizard.cloud-storage.providers.s3.description', 'wizard-icon-s3.svg', 'cloud-storage-s3', 'select-s3')
				->option('google', 'Google Cloud Storage', 'wizard.cloud-storage.providers.google.description', 'wizard-icon-google.svg', 'cloud-storage-google')
				->option('do', 'DigitalOcean Spaces', 'wizard.cloud-storage.providers.do.description', 'wizard-icon-do.svg', 'cloud-storage-do')
				->option('dreamhost', 'DreamHost Cloud Storage', 'wizard.cloud-storage.providers.dreamhost.description', 'wizard-icon-dreamhost.svg', 'cloud-storage-dreamhost')
				->option('wasabi', 'Wasabi', 'wizard.cloud-storage.providers.wasabi.description', 'wizard-icon-wasabi.png', 'cloud-storage-wasabi')
				->option('backblaze', 'Backblaze', 'wizard.cloud-storage.providers.backblaze.description', 'wizard-icon-backblaze.svg', 'cloud-storage-backblaze')
				->option('minio', 'Minio', 'wizard.cloud-storage.providers.minio.description', 'wizard-icon-minio.png', 'cloud-storage-minio')
				->option('other-s3', 'S3 Compatible', 'wizard.cloud-storage.providers.other-s3.description', 'wizard-icon-other-s3.svg', 'cloud-storage-other-s3')
			->endGroup()
		->endStep()
	->endSection()
;

S3Storage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\GoogleCloud\GoogleStorage::configureWizard($builder);
\ILAB\MediaCloud\Tools\Imgix\ImgixTool::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\S3\OtherS3Storage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\S3\DigitalOceanStorage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\S3\DreamHostStorage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\S3\WasabiStorage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\S3\MinioStorage::configureWizard($builder);
\ILAB\MediaCloud\Storage\Driver\Backblaze\BackblazeStorage::configureWizard($builder);

return $builder->build();


