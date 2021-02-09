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

namespace MediaCloud\Plugin\Tasks;

interface ITaskReporter {
	/**
	 * Open the task reporter for writing
	 */
	public function open();

	/**
	 * Writes data to the reporter
	 * @param array $data
	 */
	public function add(array $data);

	/**
	 * Closes the reporter
	 */
	public function close();

	/**
	 * Returns the header fields for the report
	 *
	 * @return array
	 */
	public function headerFields(): array;
}