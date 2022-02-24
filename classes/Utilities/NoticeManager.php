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

namespace MediaCloud\Plugin\Utilities;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class NoticeManager
 * @package MediaCloud\Plugin\Utilities
 */
class NoticeManager {
	/** @var NoticeManager  */
	private static $instance = null;

	/** @var array  */
	protected $adminNotices = [];

	protected function __construct() {
		add_action( 'admin_enqueue_scripts', function(){
			wp_enqueue_script('ilab-dismissible-notices', ILAB_PUB_JS_URL . '/ilab-dismiss-notice.js', ['jquery', 'common'], MEDIA_CLOUD_VERSION, true);
			wp_localize_script('ilab-dismissible-notices', 'ilab_dismissible_notice', ['nonce' => wp_create_nonce( 'dismissible-notice' )]);
		});

		add_action('wp_ajax_ilab_dismiss_admin_notice', [$this, 'dismissAdminNoticeAjax']);
	}

	/**
	 * Returns the static NoticeManager instance
	 * @return NoticeManager
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			$class = self::class;
			self::$instance = new $class();
		}

		return self::$instance;
	}

	public function displayGroupedAdminNotices($type, $notices, $dismissible = false, $dismissibleIdentifier = null, $dismissibleLength = 30) {
		$items = array_map(function($tag) {
			return "<li>{$tag}</li>";
		}, $notices);

		$itemsHTML = implode('', $items);
		$this->displayAdminNotice('warning', "<ol>{$itemsHTML}</ol>", $dismissible, $dismissibleIdentifier, $dismissibleLength);
	}

	public function displayAdminNotice($type, $message, $dismissible=false, $dismissibleIdentifier = null, $dismissibleLength = 30) {
		if (isset($this->adminNotices[$message])) {
			return;
		}

		if (!empty($dismissibleIdentifier) && !$this->isAdminNoticeActive($dismissibleIdentifier)) {
			return;
		}

		$this->adminNotices[$message]=true;
		$dismissibleAttr = '';
		if ($dismissible) {
			if ($dismissibleIdentifier) {
				$dismissibleAttr = "data-dismissible='$dismissibleIdentifier' data-dismissible-length='$dismissibleLength'";
			}

			$class = "notice notice-$type is-dismissible";
		} else {
			$dismissibleAttr = '';
			$class = "notice notice-$type";
		}

		if (strpos(strtolower($message), "<p>") === false) {
		    $message = "<p>$message</p>";
        }


        $action = (is_multisite() && Environment::NetworkMode()) ? 'network_admin_notices' : 'admin_notices';
		add_action($action,function() use($class, $message, $dismissibleAttr) {
			echo View::render_view( 'base/admin-notice', [
				'class' => $class,
				'message' => $message,
				'identifier' => $dismissibleAttr
			]);
		});
	}

	public function dismissAdminNotice($identifier, $length) {
		$transient = 0;

		if ( 'forever' !== $length ) {
			$length = ( 0 == absint( $length ) ) ? 1 : $length;
			$transient = absint( $length ) * DAY_IN_SECONDS;
			$length = strtotime( absint( $length ) . ' days' );
		}

		set_site_transient($identifier, $length, $transient);
	}

	public function dismissAdminNoticeAjax() {
		check_ajax_referer( 'dismissible-notice', 'nonce' );

		$this->dismissAdminNotice(sanitize_text_field($_POST['option_name']), sanitize_text_field($_POST['dismissible_length']));

		wp_die();
	}

	public function isAdminNoticeActive( $arg ) {
		$array       = explode( '-', $arg );
		$option_name = implode( '-', $array );
		$db_record   = get_site_transient( $option_name );

		if ( 'forever' == $db_record ) {
			return false;
		} elseif ( absint( $db_record ) >= time() ) {
			return false;
		} else {
			return true;
		}
	}
}
