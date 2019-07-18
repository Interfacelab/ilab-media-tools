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

namespace ILAB\MediaCloud\Tools\DynamicImages;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class WordPressUploadsAdapter extends Local {
    /** @var null|AdapterInterface */
    private $storageAdapter = null;

    public function __construct($storageAdapter) {
        $this->storageAdapter = $storageAdapter;

        parent::__construct(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'uploads');
    }

    protected function download($path) {
        if (parent::has($path)) {
            return true;
        }

        if ($this->storageAdapter->has($path)) {
            $streamData = $this->storageAdapter->readStream($path);
            if (is_array($streamData) && isset($streamData['stream'])) {
                return parent::writeStream($path, $streamData['stream'], new Config());
            }
        }

        return false;
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config) {
        if ($this->download($path)) {
            return parent::update($path, $contents, $config);
        }

        return false;
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config) {
        if ($this->download($path)) {
            return parent::updateStream($path, $resource, $config);
        }

        return false;
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath) {
        if ($this->download($path)) {
            return parent::rename($path, $newpath);
        }

        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath) {
        if ($this->download($path)) {
            return parent::copy($path, $newpath);
        }

        return false;

    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path) {
        if ($this->download($path)) {
            return parent::delete($path);
        }

        return false;
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility) {
        if ($this->download($path)) {
            return parent::setVisibility($path, $visibility);
        }

        return false;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path) {
        if (!parent::has($path)) {
            return $this->storageAdapter->has($path);
        }

        return true;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path) {
        if ($this->download($path)) {
            return parent::read($path);
        }

        return false;
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path) {
        if ($this->download($path)) {
            return parent::readStream($path);
        }

        return false;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path) {
        if ($this->download($path)) {
            return parent::getMetadata($path);
        }

        return false;
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path) {
        if ($this->download($path)) {
            return parent::getSize($path);
        }

        return false;
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path) {
        if ($this->download($path)) {
            return parent::getMimetype($path);
        }

        return false;
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path) {
        if ($this->download($path)) {
            return parent::getTimestamp($path);
        }

        return false;
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path) {
        if ($this->download($path)) {
            return parent::getVisibility($path);
        }

        return false;
    }

}