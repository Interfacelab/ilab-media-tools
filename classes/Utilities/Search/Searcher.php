<?php


namespace MediaCloud\Plugin\Utilities\Search;

use MediaCloud\Plugin\Tasks\TaskReporter;
use MediaCloud\Plugin\Tools\Imgix\ImgixTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;

class Searcher {
	private $dryRun;
	private $resetToLocal;
	private $forceImgix;
	private $forcedCDN = null;
	private $forcedDocCDN = null;
	private $tables = [];
	private $columns = [];
	private $replacer = null;
	private $imgixDomain = null;
	private $imgixKey = null;

	/** @var ImgixTool  */
	private $imgixTool = null;

	public function __construct(bool $dryRun, bool $resetToLocal, bool $forceImgix, ?string $imgixDomain, ?string $imgixKey, ?string $forcedCDN, ?string $forcedDocCDN) {
		$this->dryRun = $dryRun;

		$this->resetToLocal = $resetToLocal;
		$this->forceImgix = $forceImgix;
		$this->imgixKey = $imgixKey;
		$this->imgixDomain = $imgixDomain;
		$this->tables = static::getTables();
		$this->forcedCDN = $forcedCDN;
		$this->forcedDocCDN = $forcedDocCDN;

		if (empty($this->forceImgix)) {
			$this->forceImgix = !empty($imgixDomain);
		}

		if ($this->forceImgix) {
			$this->imgixTool = ToolsManager::instance()->tools['imgix'];
		}

		$this->replacer = new Replacer(false, null, null, null, null, null);
	}

	//region Static Helpers

	private static function isTextCol( $type ) {
		foreach ( array( 'text', 'varchar' ) as $token ) {
			if ( false !== strpos( $type, $token ) ) {
				return true;
			}
		}

		return false;
	}

	private static function escLike( $old ) {
		global $wpdb;

		// Remove notices in 4.0 and support backwards compatibility
		if ( method_exists( $wpdb, 'esc_like' ) ) {
			// 4.0
			$old = $wpdb->esc_like( $old );
		} else {
			// phpcs:ignore WordPress.WP.DeprecatedFunctions.like_escapeFound -- BC-layer for WP 3.9 or less.
			$old = like_escape( esc_sql( $old ) ); // Note: this double escaping is actually necessary, even though `escLike()` will be used in a `prepare()`.
		}

		return $old;
	}

	/**
	 * Escapes (backticks) MySQL identifiers (aka schema object names) - i.e. column names, table names, and database/index/alias/view etc names.
	 * See https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
	 *
	 * @param string|array $idents A single identifier or an array of identifiers.
	 * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
	 */
	private static function escSqlIdent( $idents ) {
		$backtick = function ( $v ) {
			// Escape any backticks in the identifier by doubling.
			return '`' . str_replace( '`', '``', $v ) . '`';
		};
		if ( is_string( $idents ) ) {
			return $backtick( $idents );
		}
		return array_map( $backtick, $idents );
	}

	/**
	 * Puts MySQL string values in single quotes, to avoid them being interpreted as column names.
	 *
	 * @param string|array $values A single value or an array of values.
	 * @return string|array A quoted string if given a string, or an array of quoted strings if given an array of strings.
	 */
	private static function escSqlValue( $values ) {
		$quote = function ( $v ) {
			// Don't quote integer values to avoid MySQL's implicit type conversion.
			if ( preg_match( '/^[+-]?[0-9]{1,20}$/', $v ) ) { // MySQL BIGINT UNSIGNED max 18446744073709551615 (20 digits).
				return esc_sql( $v );
			}

			// Put any string values between single quotes.
			return "'" . esc_sql( $v ) . "'";
		};

		if ( is_array( $values ) ) {
			return array_map( $quote, $values );
		}

		return $quote( $values );
	}

	private static function getTables() {
		global $wpdb;

		$wp_tables = [];
		$foundTables = $wpdb->get_results("SHOW TABLES FROM {$wpdb->dbname}", ARRAY_A);
		foreach($foundTables as $foundTable) {
			if (empty($foundTable)) {
				continue;
			}

			$table = array_values($foundTable)[0];
			if (strpos($table, $wpdb->prefix) !== 0) {
				continue;
			}

			$wp_tables[] = $table;
		}

//		$scope = 'all';
//		$wp_tables = array_values($wpdb->tables('all'));

		if ( ! global_terms_enabled() ) {
			// Only include sitecategories when it's actually enabled.
			$wp_tables = array_values(array_diff($wp_tables, [$wpdb->sitecategories]));
		}

		// Note: BC change 1.5.0, tables are sorted (via TABLES view).
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- uses escSqlIdent() and $wpdb->_escape().
		$query = sprintf( "SHOW TABLES WHERE %s IN ('%s')", self::escSqlIdent( 'Tables_in_' . DB_NAME ), implode( "', '", $wpdb->_escape( $wp_tables ) ) );
		$tables = $wpdb->get_col( $query );

		return $tables;
	}

	private static function getColumns($table) {
		global $wpdb;

		$table_sql       = self::escSqlIdent( $table );
		$primary_keys    = array();
		$text_columns    = array();
		$all_columns     = array();
		$suppress_errors = $wpdb->suppress_errors();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::escSqlIdent
		$results = $wpdb->get_results( "DESCRIBE $table_sql" );

		if ( ! empty( $results ) ) {
			foreach ( $results as $col ) {
				if ( 'PRI' === $col->Key ) {
					$primary_keys[] = $col->Field;
				}
				if ( self::isTextCol( $col->Type ) ) {
					$text_columns[] = $col->Field;
				}
				$all_columns[] = $col->Field;
			}
		}
		$wpdb->suppress_errors( $suppress_errors );
		return array( $primary_keys, $text_columns, $all_columns );
	}

	//endregion
	
	private function performReplace($col, $primary_keys, $table, $old, $new) {
		global $wpdb;

		$count    = 0;
		$table_sql        = self::escSqlIdent( $table );
		$col_sql          = self::escSqlIdent( $col );
		$where            = " WHERE $col_sql" . $wpdb->prepare( ' LIKE BINARY %s', '%' . self::escLike( $old ) . '%' );
		if ($table === $wpdb->postmeta)  {
			$where .= " AND `meta_key` <> '_wp_attachment_metadata' AND `meta_key` <> 'ilab_s3_info'";
		}
		$primary_keys_sql = implode( ',', self::escSqlIdent( $primary_keys ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::escSqlIdent
		$rows = $wpdb->get_results( "SELECT {$primary_keys_sql} FROM {$table_sql} {$where}" );

		foreach ( $rows as $keys ) {
			$where_sql = '';
			foreach ( (array) $keys as $k => $v ) {
				if ( strlen( $where_sql ) ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= self::escSqlIdent( $k ) . ' = ' . self::escSqlValue( $v );
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::escSqlIdent
			$col_value = $wpdb->get_var( "SELECT {$col_sql} FROM {$table_sql} WHERE {$where_sql}" );

			if ( '' === $col_value ) {
				continue;
			}

			$value = $this->replacer->run($old, $new, $col_value);

			if ( $value === $col_value ) {
				continue;
			}

			if ( $this->dryRun ) {
				$count++;
			} else {
				$where = array();
				foreach ( (array) $keys as $k => $v ) {
					$where[ $k ] = $v;
				}

				$count += $wpdb->update( $table, array( $col => $value ), $where );
			}
		}

		return $count;
	}

	private function getAttachmentUrl(\Closure $callback) {
		if ($this->resetToLocal) {
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
			add_filter('media-cloud/storage/override-url', '__return_false', PHP_INT_MAX);
			add_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);
		}

		$result = $callback();

		if ($this->resetToLocal) {
			remove_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);
			remove_filter('media-cloud/storage/override-url', '__return_false', PHP_INT_MAX);
			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
		}

		return $result;
	}

	private function getUntreatedUrl(array &$urlMap, $attachmentUrl, \Closure $callback) {
		$results = [];

		$uploadDir = wp_get_upload_dir();

		// local => current
		{
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
			add_filter('media-cloud/storage/override-url', '__return_false', PHP_INT_MAX);
			add_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);

			$result = $callback();
			if (strpos($result, $uploadDir['baseurl']) !== 0) {
				$urlParts = parse_url($result);
				$result = str_replace("{$urlParts['scheme']}://{$urlParts['host']}", $uploadDir['baseurl'], $result);
			}

			if (!empty($result) && ($result != $attachmentUrl)) {
				$urlMap[$result] = $attachmentUrl;

				$results[] = $result;
			}

			remove_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);
			remove_filter('media-cloud/storage/override-url', '__return_false', PHP_INT_MAX);
			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
		}

		if (apply_filters('media-cloud/imgix/enabled', false)) {
			// cloud => current
			{
				add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
				add_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);

				$result = $callback();

				if (!empty($result) && ($result != $attachmentUrl)) {
					$urlMap[$result] = $attachmentUrl;

					$results[] = $result;
				}

				remove_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);
				remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
			}
		}

		// imgix => current
		if ($this->forceImgix) {
			add_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);

			if ($this->imgixDomain) {
				add_filter('media-cloud/dynamic-images/override-domain', function($domain) {
					return $this->imgixDomain;
				});
			}

			if ($this->imgixKey) {
				add_filter('media-cloud/dynamic-images/override-key', function($key) {
					return $this->imgixKey;
				});
			}

			$this->imgixTool->forceEnable(true);

			$result = $callback();

			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;

				$results[] = $result;
			}

			$this->imgixTool->forceEnable(false);
			remove_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);

			remove_all_filters('media-cloud/dynamic-images/override-domain');
			remove_all_filters('media-cloud/dynamic-images/override-key');
		}

		// old.cdn => current
		if (!empty($this->forcedCDN)) {
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);

			add_filter('media-cloud/storage/override-cdn', function($cdn) {
				return $this->forcedCDN;
			});

			add_filter('media-cloud/storage/override-doc-cdn', function($cdn) {
				return $this->forcedDocCDN;
			});

			$result = $callback();

			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;

				$results[] = $result;
			}

			remove_all_filters('media-cloud/storage/override-cdn');
			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
		}

		// old.doc.cdn => current
		if (!empty($this->forcedDocCDN) && ($this->forcedCDN !== $this->forcedDocCDN)) {
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);

			add_filter('media-cloud/storage/override-doc-cdn', function($cdn) {
				return $this->forcedDocCDN;
			});

			$result = $callback();

			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;

				$results[] = $result;
			}

			remove_all_filters('media-cloud/storage/override-doc-cdn');
			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
		}

		// storage => current
		{
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
			add_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);

			$result = $callback();
			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;

				$results[] = $result;
			}

			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
			remove_filter('media-cloud/storage/ignore-cdn', '__return_true', PHP_INT_MAX);
		}


		// cdn => current
		{
			add_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);

			$result = $callback();
			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;
			}

			remove_filter('media-cloud/dynamic-images/skip-url-generation', '__return_true', PHP_INT_MAX);
		}

		if ($this->resetToLocal) {
			$result = $callback();
			if (!empty($result) && ($result != $attachmentUrl) && !in_array($result, $results)) {
				$urlMap[$result] = $attachmentUrl;
			}
		}
	}

	public function searchAndReplace(string $old, string $new) {
		$total = 0;
		$skipColumns = [
			'guid', 'user_pass'
		];

		foreach ($this->tables as $table) {
			if (!isset($this->columns[$table])) {
				$this->columns[$table] = static::getColumns( $table );
			}

			list( $primary_keys, $columns, $all_columns ) = $this->columns[$table];

			// since we'll be updating one row at a time,
			// we need a primary key to identify the row
			if (empty($primary_keys)) {
				continue;
			}

			foreach($columns as $col) {
				if (in_array( $col, $skipColumns, true)) {
					continue;
				}

				$count = $this->performReplace( $col, $primary_keys, $table, $old, $new );

				$total += $count;
			}
		}

		Logger::info("Replacing $old with $new $total times.", [], __METHOD__, __LINE__);
		return $total;
	}


	public function replacePostId($postId, array $sizes, TaskReporter $reporter, \Closure $prepCallback) {
		$mime = get_post_mime_type($postId);
		$urlMap = [];

		if (strpos($mime, 'image/') === 0) {
			foreach($sizes as $sizeKey => $sizeData) {
				$attachmentUrl = $this->getAttachmentUrl(function() use ($postId, $sizeKey) {
					return wp_get_attachment_image_url($postId, $sizeKey);
				});

				if (!empty($attachmentUrl)) {
					$this->getUntreatedUrl($urlMap, $attachmentUrl, function() use ($postId, $sizeKey) {
						return wp_get_attachment_image_url($postId, $sizeKey);
					});
				}
			}

			$attachmentUrl = $this->getAttachmentUrl(function() use ($postId) {
				return wp_get_attachment_url($postId);
			});

			if (!empty($attachmentUrl)) {
				$this->getUntreatedUrl($urlMap, $attachmentUrl, function() use ($postId) {
					return wp_get_attachment_url($postId);
				});
			}

			$originalImage = $this->getAttachmentUrl(function() use ($postId) {
				return wp_get_original_image_url($postId);
			});

			if (!empty($originalImage)) {
				$this->getUntreatedUrl($urlMap, $originalImage, function() use ($postId) {
					return wp_get_original_image_url($postId);
				});
			}
		} else {
			$attachmentUrl = $this->getAttachmentUrl(function() use ($postId) {
				return wp_get_attachment_url($postId);
			});

			if (!empty($attachmentUrl)) {
				$this->getUntreatedUrl($urlMap, $attachmentUrl, function() use ($postId) {
					return wp_get_attachment_url($postId);
				});
			}
		}

		$prepCallback();

		$totalChanges = 0;
		foreach($urlMap as $old => $new) {
			$result = $this->searchAndReplace($old, $new);
			$totalChanges += intval($result);

			$result = $this->searchAndReplace(str_replace('/', '\/', $old), str_replace('/', '\/', $new));
			$totalChanges += intval($result);

			$reporter->add([
				$postId,
				$old,
				$new,
				intval($result)
			]);
		}

		return $totalChanges;
	}

}