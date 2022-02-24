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

use MediaCloud\Vendor\duncan3dc\Laravel\BladeInstance;

if (!defined('ABSPATH')) { header('Location: /'); die; }

final class View {
	/** @var BladeInstance|null  */
    private static $bladeInstance = null;
    private static $registeredViewDirectories = [];

    private function __construct($view) {
    }

    private static function getTempDir() {
        $temp = Environment::Option(null, ['MCLOUD_VIEW_CACHE', 'ILAB_MEDIA_CLOUD_VIEW_CACHE'], null);
        if (!empty($temp)) {
            return trailingslashit($temp).'mcloud-view-cache';
        } else {
	        $result = trailingslashit(WP_CONTENT_DIR).'mcloud-view-cache';
        }

        if (!file_exists($result)) {
        	@mkdir($result, 0777,true);
        }


        if (!file_exists($result)) {
        	$result = '/tmp/mcloud-view-cache';
	        @mkdir($result, 0777,true);
        }

        return $result;
    }

    private static function bladeInstance() {
        if (static::$bladeInstance == null) {
            $cacheDir = static::getTempDir();

            static::$bladeInstance = new BladeInstance(ILAB_VIEW_DIR, $cacheDir);

            foreach(static::$registeredViewDirectories as $directory) {
            	static::$bladeInstance->addPath($directory);
            }

	        static::$bladeInstance->directive('inline', function($expression) {
		        return '<?php \MediaCloud\Plugin\Utilities\View::InlineImage('.$expression.'); ?>';
	        });

	        static::$bladeInstance->directive('network', function($expression) {
		        return '<?php if (is_multisite() && \MediaCloud\Plugin\Utilities\Environment::NetworkMode()): ?>';
	        });

	        static::$bladeInstance->directive('endnetwork', function($expression) {
		        return '<?php endif; ?>';
	        });

	        static::$bladeInstance->directive('inline', function($expression) {
		        return '<?php \MediaCloud\Plugin\Utilities\View::InlineImage('.$expression.'); ?>';
	        });

	        static::$bladeInstance->directive('plan', function($expression) {
	        	return "<?php if (\\MediaCloud\\Plugin\\Utilities\\LicensingManager::ActivePlan($expression)): ?>";
	        });

	        static::$bladeInstance->directive('elseplan', function($expression) {
		        return "<?php elseif (\\MediaCloud\\Plugin\\Utilities\\LicensingManager::ActivePlan($expression)): ?>";
	        });

	        static::$bladeInstance->directive('endplan', function($expression) {
		        return '<?php endif; ?>';
	        });

	        static::$bladeInstance->directive('track', function($expression) {
		        return "<?php if (\\MediaCloud\\Plugin\\Utilities\\LicensingManager::OptedIn($expression)): ?>";
	        });

	        static::$bladeInstance->directive('elsetrack', function($expression) {
		        return "<?php else: ?>";
	        });

	        static::$bladeInstance->directive('endtrack', function($expression) {
		        return '<?php endif; ?>';
	        });
        }

        return static::$bladeInstance;
    }

    public static function render_view($view, $data) {
        if (strpos($view, '.php') == (strlen($view) - 4)) {
            $view = substr($view, 0,  (strlen($view) - 4));
        }

        return self::bladeInstance()->render(str_replace('.php', '', $view), $data);
    }

    public static function InlineImage($image) {
    	$imageFile = ILAB_PUB_IMG_DIR.'/'.$image;
    	if (file_exists($imageFile)) {
    		echo str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', file_get_contents($imageFile));
	    }
    }

    public static function registerViewDirectory($directory) {
    	static::$registeredViewDirectories[] = $directory;
    }
}
