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

namespace MediaCloud\Plugin\Tools;

use MediaCloud\Plugin\Utilities\Environment;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;
use function MediaCloud\Plugin\Utilities\arrayPath;

/**
 * Manages migrating options/settings between major plugin versions
 *
 * @package MediaCloud\Plugin\Tools
 */
final class MigrationsManager {
    /** @var MigrationsManager The current instance */
    private static $instance;

    /** @var array  */
    private $config = [];

    /** @var array  */
    private $deprecatedErrors = [];

    //region Constructor
    public function __construct() {
        $configFile = ILAB_CONFIG_DIR.'/migrations/migrations.php';
        if (file_exists($configFile)) {
            $this->config = include $configFile;
        } else {
            Logger::warning("Could not find migrations config '$configFile'.", [], __METHOD__, __LINE__);
        }
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            $class=__CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }
    //endregion

    //region Migration

    /**
     * Migrates all tools
     */
    public function migrate($force = false) {
    	$lastVersion = Environment::Option('mcloud_migration_last_version', null, null);

    	$processed = false;
    	foreach($this->config as $version => $versionData) {
    	    if (empty($force) && ($lastVersion != null)) {
    	    	$lastVersionParts = explode('.', $lastVersion);
    	    	$versionParts = explode('.', $version);

		        $lastVersionNum = sprintf('%d%04d%04d', $lastVersionParts[0], $lastVersionParts[1], $lastVersionParts[2]);
		        $versionNum = sprintf('%d%04d%04d', $versionParts[0], $versionParts[1], $versionParts[2]);

		        if ($versionNum <= $lastVersionNum) {
		        	continue;
		        }
	        }

    	    $processed = true;

    	    $this->processMigration($version, $versionData, false, false, false);

    	    Environment::UpdateOption('mcloud_migration_last_version', $version);
	    }

    	if (!empty($this->deprecatedErrors)) {
		    Environment::UpdateOption('mcloud_migration_deprecated_errors', $this->deprecatedErrors);
	    }

    	if (!$processed) {
    		$this->deprecatedErrors = Environment::Option('mcloud_migration_deprecated_errors', null, []);
    		if (!empty($this->deprecatedErrors)) {
				$this->deprecatedErrors = [];
			    foreach($this->config as $version => $versionData) {
				    $this->processMigration($version, $versionData, true, true, false);
			    }

			    if (empty($this->deprecatedErrors)) {
				    Environment::DeleteOption('mcloud_migration_deprecated_errors');
			    }
		    }
	    }

    }

    private function processMigration($version, $migrationData, $skipCopy, $skipTransition, $skipDeprecated) {
    	if (!$skipCopy) {
		    $copy = arrayPath($migrationData, 'copy', []);
		    if (!empty($copy)) {
			    Environment::CopyOptions($copy);
		    }
	    }

	    $transition = arrayPath($migrationData, 'transition', []);
    	if (!$skipTransition) {
		    if(!empty($transition)) {
			    Environment::TransitionOptions($transition);
		    }
	    }

    	if (!$skipDeprecated) {
		    $transitionEnv = [];
		    foreach($transition as $old => $new) {
			    $transitionEnv[strtoupper(str_replace('-', '_', $old))] = strtoupper(str_replace('-', '_', $new));
		    }

		    $deprecated = arrayPath($migrationData, 'deprecated', []);
		    $deprecated = array_merge($transitionEnv, $deprecated);
		    if (!empty($deprecated)) {
			    $deprecatedVars = Environment::DeprecatedEnvironmentVariables($deprecated);
			    if (is_array($deprecatedVars) && !empty($deprecatedVars)) {
				    $this->deprecatedErrors = array_merge($this->deprecatedErrors, $deprecatedVars);
			    }
		    }
	    }
    }
    //endregion

    //region Utilities
    /**
     * Checks to see if deprecated environment variables exist for a specific tool
     * @return bool
     */
    public function hasDeprecatedEnvironment() {
        return !empty($this->deprecatedErrors);
    }

    /**
     * Displays migration errors
     */
    public function displayMigrationErrors() {
    	if (empty($this->deprecatedErrors)) {
    		return;
	    }

	    $lastVersion = Environment::Option('mcloud_migration_last_version', null, '3.0.0');

	    $exist = [];
	    foreach($this->deprecatedErrors as $oldEndVar => $newEnvVar) {
		    $exist[] = "<li><code>$oldEndVar</code> is now <code>$newEnvVar</code></li>";
	    }

	    $message = "You have have outdated environmental variables defined.  Please try to change them as soon as possible.  The deprecated environment variables are: <ul>";
	    $message .= implode("\n", $exist);
	    $message .= '</ul>';

        NoticeManager::instance()->displayAdminNotice('error', $message,true, 'mcloud-deprecated-env-'.str_replace('.','_',$lastVersion), 1);
    }
    //endregion
}