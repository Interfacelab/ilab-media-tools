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

namespace ILAB\MediaCloud\Utilities;

use duncan3dc\Laravel\BladeInstance;

if (!defined('ABSPATH')) { header('Location: /'); die; }

final class View {
	/** @var BladeInstance|null  */
    private static $bladeInstance = null;
    private static $registeredViewDirectories = [];

    private function __construct($view) {
    }

    private static function getTempDir() {
        $temp = Environment::Option(null, 'ILAB_MEDIA_CLOUD_VIEW_CACHE', null);
        if (!empty($temp)) {
            return trailingslashit($temp);
        }

        if (defined('WP_TEMP_DIR')) {
            return trailingslashit(WP_TEMP_DIR);
        }

        if (function_exists('sys_get_temp_dir')) {
            $temp = sys_get_temp_dir();
            if (@is_dir($temp) && wp_is_writable($temp)) {
                return trailingslashit($temp);
            }
        }

        $temp = ini_get('upload_tmp_dir');
        if (!empty($temp) && @is_dir($temp) && wp_is_writable($temp)) {
            return trailingslashit($temp);
        }

        $temp = WP_CONTENT_DIR . '/';
        if (@is_dir($temp) && wp_is_writable($temp)) {
            return $temp;
        }

        $temp = '/tmp/';
        if (@is_dir($temp) && wp_is_writable($temp)) {
            return $temp;
        }

        return null;
    }

    private static function bladeInstance() {
        if (static::$bladeInstance == null) {
            $cacheDir = static::getTempDir();

            static::$bladeInstance = new BladeInstance(ILAB_VIEW_DIR, $cacheDir.'media-cloud-views');

            foreach(static::$registeredViewDirectories as $directory) {
            	static::$bladeInstance->addPath($directory);
            }

	        static::$bladeInstance->directive('inline', function($expression) {
		        return '<?php \ILAB\MediaCloud\Utilities\View::InlineImage('.$expression.'); ?>';
	        });

	        static::$bladeInstance->directive('network', function($expression) {
		        return '<?php if (is_multisite() && \ILAB\MediaCloud\Utilities\Environment::NetworkMode()): ?>';
	        });

	        static::$bladeInstance->directive('endnetwork', function($expression) {
		        return '<?php endif; ?>';
	        });

	        static::$bladeInstance->directive('inline', function($expression) {
		        return '<?php \ILAB\MediaCloud\Utilities\View::InlineImage('.$expression.'); ?>';
	        });

	        static::$bladeInstance->directive('plan', function($expression) {
	        	return "<?php if (\\ILAB\\MediaCloud\\Utilities\\LicensingManager::ActivePlan($expression)): ?>";
	        });

	        static::$bladeInstance->directive('elseplan', function($expression) {
		        return "<?php elseif (\\ILAB\\MediaCloud\\Utilities\\LicensingManager::ActivePlan($expression)): ?>";
	        });

	        static::$bladeInstance->directive('endplan', function($expression) {
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
