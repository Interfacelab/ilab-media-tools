<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface ProviderInterface
{
    /**
     * @return mixed
     */
    public function getOsRelease();

    /**
     * @return mixed
     */
    public function getOsType();

    /**
     * @return mixed
     */
    public function getOsKernelVersion();

    /**
     * @return string
     */
    public function getArchitecture();

    /**
     * @param \PDO $connection
     * @return mixed
     */
    public function getDbVersion(\PDO $connection);

    /**
     * @param \PDO $connection
     * @return mixed
     */
    public function getDbInfo(\PDO $connection);

    /**
     * @param \PDO $connection
     * @return mixed
     */
    public function getDbType(\PDO $connection);

    /**
     * Total Memory in bytes
     * @return int|null
     */
    public function getTotalMem();

    /**
     * Free Memory in bytes
     * @return int|null
     */
    public function getFreeMem();

    /**
     * Used Memory in bytes
     * @return int|null
     */
    public function getUsedMem();

    /**
     * Total Swap in bytes
     * @return int|null
     */
    public function getTotalSwap();

    /**
     * Free Swap in bytes
     * @return int|null
     */
    public function getFreeSwap();

    /**
     * Used Swap in bytes
     * @return int|null
     */
    public function getUsedSwap();

    /**
     * @return string
     */
    public function getHostname();

    /**
     * @return bool
     */
    public function isLinuxOs();

    /**
     * @return bool
     */
    public function isWindowsOs();

    /**
     * @return bool
     */
    public function isBsdOs();

    /**
     * @return bool
     */
    public function isMacOs();

    /**
     * @return int|null
     */
    public function getUptime();

    /**
     * @return int|null
     */
    public function getPhysicalCpus();

    /**
     * @return int|null
     */
    public function getCpuCores();

    /**
     * @return int|null
     */
    public function getCpuPhysicalCores();

    /**
     * @return string|null
     */
    public function getCpuModel();

    /**
     * @return string|null
     */
    public function getCpuVendor();

    /**
     * @return array
     */
    public function getCpuUsage();

    /**
     * @return mixed
     */
    public function getServerIP();

    /**
     * @return string
     */
    public function getExternalIP();

    /**
     * @return mixed
     */
    public function getServerSoftware();

    /**
     * @return bool
     */
    public function isISS();

    /**
     * @return bool
     */
    public function isNginx();

    /**
     * @return bool
     */
    public function isApache();

    /**
     * @param int $what
     * @return string
     */
    public function getPhpInfo($what = -1);

    /**
     * @return string
     */
    public function getPhpVersion();

    /**
     * @return array
     */
    public function getPhpDisabledFunctions();

    /**
     * @return array
     */
    public function getPhpModules();

    /**
     * @param string $module
     * @return bool
     */
    public function isPhpModuleLoaded($module);

    /**
     * @param array $hosts
     * @param int $count
     * @return array
     */
    public function getPing(array $hosts = null, $count = 2);

    /**
     * Retrieves data from $_SERVER array
     * @param $key
     * @return mixed|null
     */
    public function getServerVariable($key);

    /**
     * @return mixed
     */
    public function getPhpSapiName();

    /**
     * @return bool
     */
    public function isCli();

    /**
     * @return bool
     */
    public function isFpm();

    /**
     * @return mixed
     */
    public function getDiskUsage();

    /**
     * @return mixed
     */
    public function getDiskTotal();

    /**
     * @return mixed
     */
    public function getDiskFree();
}
