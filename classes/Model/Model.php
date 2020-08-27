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

namespace MediaCloud\Plugin\Model;

/**
 * Represents a database model
 */
abstract class Model {
	//region Constants

	const MODEL_NEW = 0;
	const MODEL_LIVE = 1;
	const MODEL_DIRTY = 2;
	const MODEL_DELETED = 3;

	//endregion

	//region Fields

	/**
	 * The state of the model
	 * @var int
	 */
	protected $modelState = self::MODEL_NEW;

	/**
	 * The model's database id
	 * @var int|null
	 */
	protected $id = null;

	/**
	 * Model property names and type specifier, must map to the database and must be properties on the model class.
	 * Will be used in magic __get and __set.
	 *
	 * @var string[]
	 */
	protected $modelProperties = [];

	/**
	 * Name of properties that are serialized, must map to database and must be properties on model class.
	 *
	 * @var array
	 */
	protected $serializedProperties = [];

	/**
	 * Name of properties that are JSON serialized, must map to database and must be properties on model class.
	 *
	 * @var array
	 */
	protected $jsonProperties = [];

	/**
	 * Map of column name to property names
	 * @var array
	 */
	protected $columnMap = [];

	//endregion

	//region Static Methods


	/**
	 * The name of the database table
	 * @return string
	 * @throws \Exception
	 */
	public static function table() {
		throw new \Exception("Not implemented.");
	}

	//endregion

	//region Init

	/**
	 * Model constructor.
	 *
	 * @param object|array|null $data
	 */
	public function __construct($data = null) {
		if (!empty($data)) {
			$this->modelState = self::MODEL_LIVE;

			if (is_array($data)) {
				$data = (object)$data;
			}

			if (property_exists($data, 'id')) {
				$this->id = intval($data->id);
			}

			$modelProps = array_keys($this->modelProperties);
			foreach($modelProps as $prop) {
				$propName = $prop;

				if (isset($this->columnMap[$prop])) {
					$propName = $this->columnMap[$prop];
				}

				if (property_exists($data, $propName)) {
					if (in_array($prop, $this->serializedProperties)) {
						$val = $data->{$propName};
						$this->{$prop} = maybe_unserialize($val);
					} else if (in_array($prop, $this->jsonProperties)) {
						if (!empty($data->{$propName})) {
							$this->{$prop} = json_decode($data->{$propName}, true);
						}
					} else {
						$this->{$prop} = $data->{$propName};
					}
				}
			}
		}
	}

	//endregion

	//region Magic

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get($name) {
		if (in_array($name, array_keys($this->modelProperties))) {
			return $this->{$name};
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		if (in_array($name, array_keys($this->modelProperties))) {
			$this->{$name} = $value;
			if ($this->modelState == self::MODEL_LIVE) {
				$this->modelState = self::MODEL_DIRTY;
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name) {
		if (in_array($name, array_keys($this->modelProperties))) {
			return true;
		}

		return false;
	}

	//endregion

	//region Properties

	/**
	 * Model's ID
	 * @return int|null
	 */
	public function id() {
		return $this->id;
	}

	//endregion

	//region Saving/Deleting

	/**
	 * Saves or updates the model
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function save()  {
		if ($this->modelState == self::MODEL_DELETED) {
			return false;
		}

		global $wpdb;

		$data = [];
		$formats = [];

		foreach($this->modelProperties as $prop => $format) {
			$colName = $prop;

			if (isset($this->columnMap[$prop])) {
				$colName = $this->columnMap[$prop];
			}

			if (in_array($prop, $this->serializedProperties)) {
				$data[$colName] = maybe_serialize($this->{$prop});
				$formats[] = '%s';
			} else if (in_array($prop, $this->jsonProperties)) {
				if (!empty($this->{$prop})) {
					$data[$colName] = json_encode($this->{$prop}, JSON_PRETTY_PRINT);
				} else {
					$data[$colName] = null;
				}

				$formats[] = '%s';
			} else {
				$data[$colName] = $this->{$prop};
				$formats[] = $format;
			}
		}

		if ($this->modelState == self::MODEL_NEW) {
			$result = $wpdb->insert(static::table(), $data, $formats);
			if (!empty($result)) {
				$this->id = $wpdb->insert_id;
				$this->modelState = self::MODEL_LIVE;
				return true;
			} else {
				$lastError = $wpdb->last_error;
				error_log($lastError);
			}
		} else {
			$result = $wpdb->update(static::table(), $data, ['id' => $this->id], $formats);
			if (!empty($result)) {
				$this->modelState = self::MODEL_LIVE;
				return true;
			} else {
				$error = $wpdb->last_error;
				$dafuk = 'what';
			}
		}

		return false;
	}

	/**
	 * Called before the model deletes itself
	 */
	protected function willDelete() {
	}

	/**
	 * Deletes the model.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function delete() {
		if (($this->modelState == self::MODEL_DELETED) || ($this->modelState == self::MODEL_NEW)) {
			return false;
		}

		$this->willDelete();

		global $wpdb;

		$result = $wpdb->delete(static::table(), ['id' => $this->id]);
		if (!empty($result)) {
			$this->modelState = self::MODEL_DELETED;
			return true;
		}

		return false;
	}

	//endregion

	//region Queries
	/**
	 * Returns a task with the given ID
	 *
	 * @param int $id
	 *
	 * @return Model|null
	 * @throws \Exception
	 */
	public static function instance($id) {
		global $wpdb;

		$table = static::table();
		$rows = $wpdb->get_results($wpdb->prepare("select * from {$table} where id = %d", $id));

		foreach($rows as $row) {
			return new static($row);
		}

		return null;
	}

	/**
	 * Returns all of the models
	 *
	 * @param null|string $orderBy
	 * @param int $limit
	 * @param int $page
	 *
	 * @return Model|null
	 * @throws \Exception
	 */
	public static function all($orderBy = null, $limit = 0, $page = 0) {
		global $wpdb;

		$table = static::table();

		$query = "select * from {$table}";

		if (!empty($orderBy)) {
			$query = "{$query} order by {$orderBy}";
		}

		if (!empty($limit)) {
			$offset = ($page * $limit);
			$query = "{$query} limit {$limit} offset {$offset}";
		}

		$rows = $wpdb->get_results($query);

		foreach($rows as $row) {
			return new static($row);
		}

		return null;
	}
	//endregion
}
