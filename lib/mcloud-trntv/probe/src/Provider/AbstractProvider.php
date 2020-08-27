<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @return string
     */
    public function getPhpVersion()
    {
        return phpversion();
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return php_uname('n');
    }

    /**
     * @return string
     */
    public function getArchitecture()
    {
        return php_uname('m');
    }

    /**
     * @return bool
     */
    public function isLinuxOs()
    {
        return $this->getOsType() === 'Linux';
    }

    /**
     * @return bool
     */
    public function isWindowsOs()
    {
        return $this->getOsType() === 'Windows';
    }

    /**
     * @return bool
     */
    public function isBSDOs()
    {
        return $this->getOsType() === 'BSD';
    }

    /**
     * @return bool
     */
    public function isMacOs()
    {
        return $this->getOsType() === 'Mac';
    }

    /**
     * @return mixed
     */
    public function getServerIP()
    {
        return $this->isISS() ? $this->getServerVariable('LOCAL_ADDR') : $this->getServerVariable('SERVER_ADDR');
    }

    /**
     * @return string
     */
    public function getExternalIP()
    {
        $cmd = 'dig +short myip.opendns.com @resolver1.opendns.com';
        exec($cmd, $output);
        if (is_array($output) && !empty($output)) {
            return trim($output[0]);
        }

        $cmd = 'curl api.ipify.org';
        return shell_exec($cmd);
    }

    /**
     * @return mixed
     */
    public function getServerSoftware()
    {
        return $this->getServerVariable('SERVER_SOFTWARE');
    }

    /**
     * @return bool
     */
    public function isISS()
    {
        return strpos(strtolower($this->getServerSoftware()), 'microsoft-iis') !== false;
    }

    /**
     * @return bool
     */
    public function isNginx()
    {
        return strpos(strtolower($this->getServerSoftware()), 'nginx') !== false;
    }

    /**
     * @return bool
     */
    public function isApache()
    {
        return strpos(strtolower(self::getServerSoftware()), 'apache') !== false;
    }

    /**
     * @param int $what
     * @return string
     */
    public function getPhpInfo($what = -1)
    {
        ob_start();
        phpinfo($what);
        return ob_get_clean();
    }

    /**
     * @return array
     */
    public function getPhpDisabledFunctions()
    {
        return array_map('trim', explode(',', ini_get('disable_functions')));
    }

    /**
     * @inheritdoc
     */
    public function getPhpModules()
    {
        return get_loaded_extensions();
    }

    /**
     * @inheritdoc
     */
    public function isPhpModuleLoaded($module)
    {
        return extension_loaded($module);
    }

    /**
     * @param array $hosts
     * @param int $count
     * @return array
     */
    public function getPing(array $hosts = null, $count = 2)
    {
        if (!$hosts) {
            $hosts = array('gnu.org', 'github.com', 'wikipedia.org');
        }
        $ping = [];
        for ($i = 0; $i < count($hosts); $i++) {
            $command = "/bin/ping -qc {$count} {$hosts[$i]} | awk -F/ '/^rtt/ { print $5 }'";
            $result = array();
            exec($command, $result);
            $ping[$hosts[$i]] = isset($result[0]) ? $result[0] : false;
        }
        return $ping;
    }

    /**
     * @param \PDO $connection
     * @return mixed
     */
    public function getDbInfo(\PDO $connection)
    {
        return $connection->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * @param \PDO $connection
     * @return mixed
     */
    public function getDbType(\PDO $connection)
    {
        return $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @param $connection
     * @return string
     */
    public function getDbVersion(\PDO $connection)
    {
        if (is_a($connection, 'PDO')) {
            return $connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } else {
            return mysqli_get_server_info($connection);
        }
    }

    /**
     * Retrieves data from $_SERVER array
     * @param $key
     * @return mixed|null
     */
    public function getServerVariable($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    /**
     * @return string
     */
    public function getPhpSapiName()
    {
        return php_sapi_name();
    }

    /**
     * @return bool
     */
    public function isFpm()
    {
        return strtolower(substr($this->getPhpSapiName(), 0, 3)) === 'fpm';
    }

    /**
     * @return bool
     */
    public function isCli()
    {
        return strtolower(substr($this->getPhpSapiName(), 0, 3)) === 'cli';
    }
}
