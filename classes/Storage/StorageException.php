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

namespace ILAB\MediaCloud\Storage;

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Class StorageException
 * @package ILAB\MediaCloud\Storage
 */
class StorageException extends \Exception {

	/**
	 * @param \Exception $ex
	 *
	 * @throws StorageException
	 */
	public static function ThrowFromOther($ex) {
		throw new StorageException($ex->getMessage(), $ex->getCode(), $ex);
	}

}