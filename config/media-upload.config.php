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
    "name" => "Direct Uploads",
	"title" => "Direct Uploads",
	"description" => "Provides an easy to use tool for uploading media directly to Amazon S3, Minio or Google Cloud Storage.",
	"class" => "ILAB\\MediaCloud\\Tools\\MediaUpload\\UploadTool",
	"dependencies" => ["storage", "imgix"],
	"env" => "ILAB_MEDIA_UPLOAD_ENABLED"
];