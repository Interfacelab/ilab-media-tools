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

namespace ILAB\MediaCloud\Storage\Driver\Backblaze;

use ILAB\B2\Client;
use ILAB\B2\File;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\StreamWrapper;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;

if(!defined('ABSPATH')) {
    header('Location: /');
    die;
}

class BackblazeAdapter extends AbstractAdapter {
    use NotSupportingVisibilityTrait;

    /** @var Client */
    private $client;
    private $bucketName;

    public function __construct($client, $bucketName) {
        $this->client = $client;
        $this->bucketName = $bucketName;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path) {
        return $this->client->fileExists(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config) {
        $file = $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $contents
        ]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config) {
        $file = $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $resource
        ]);
        
        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        $file = $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $contents
        ]);
        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        $file = $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => $resource
        ]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path) {
        $file = $this->client->getFile([
            'BucketName' => $this->bucketName,
            'FileName' => $path
        ]);

        $fileContent = $this->client->download([
            'FileId' => $file->getId()
        ]);

        return ['contents' => $fileContent];
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path) {
        $stream = stream_for();
        $download = $this->client->download([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'SaveAs' => $stream,
        ]);
        $stream->seek(0);
        try {
            $resource = StreamWrapper::getResource($stream);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return $download === true ? ['stream' => $resource] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath) {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newPath) {
        return $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $newPath,
            'Body' => @file_get_contents($path)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path) {
        return $this->client->deleteFile(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($path) {
        return $this->client->deleteFile(['FileName' => $path, 'BucketName' => $this->bucketName]);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($path, Config $config) {
        return $this->client->upload([
            'BucketName' => $this->bucketName,
            'FileName' => $path,
            'Body' => ''
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path) {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path) {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path) {
        $file = $this->client->getFile(['FileName' => $path, 'BucketName' => $this->bucketName]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path) {
        $file = $this->client->getFile(['FileName' => $path, 'BucketName' => $this->bucketName]);

        return $this->getFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false) {
        $fileObjects = $this->client->listFiles([
            'BucketName' => $this->bucketName,
        ]);
        if ($recursive === true && $directory === '') {
            $regex = '/^.*$/';
        } else if ($recursive === true && $directory !== '') {
            $regex = '/^' . preg_quote($directory) . '\/.*$/';
        } else if ($recursive === false && $directory === '') {
            $regex = '/^(?!.*\\/).*$/';
        } else if ($recursive === false && $directory !== '') {
            $regex = '/^' . preg_quote($directory) . '\/(?!.*\\/).*$/';
        } else {
            throw new \InvalidArgumentException();
        }
        $fileObjects = array_filter($fileObjects, function ($fileObject) use ($directory, $regex) {
            return 1 === preg_match($regex, $fileObject->getName());
        });
        $normalized = array_map(function ($fileObject) {
            return $this->getFileInfo($fileObject);
        }, $fileObjects);
        return array_values($normalized);
    }

    /**
     * Get file info
     *
     * @param $file File
     *
     * @return array
     */

    protected function getFileInfo($file) {
        $normalized = [
            'type' => 'file',
            'path' => $file->getName(),
            'timestamp' => substr($file->getUploadTimestamp(), 0, -3),
            'size' => $file->getSize()
        ];

        return $normalized;
    }
}