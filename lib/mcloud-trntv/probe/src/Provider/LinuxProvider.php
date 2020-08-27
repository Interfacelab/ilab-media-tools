<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * Linux information provider
 * @author Eugene Terentev <eugene@terentev.net>
 */
class LinuxProvider extends AbstractUnixProvider
{
    /**
     * @var array|null
     */
    protected $cpuInfo;
    /**
     * @var
     */
    protected $cpuInfoByLsCpu;

    /**
     * @inheritdoc
     */
    public function getUptime()
    {
        $uptime = file_get_contents('/proc/uptime');
        $uptime = explode('.', $uptime);
        return (int) array_shift($uptime);
    }

    /**
     * @inheritdoc
     */
    public function getOsRelease()
    {
        return shell_exec('/usr/bin/lsb_release -ds');
    }

    /**
     * @inheritdoc
     */
    public function getOsKernelVersion()
    {
        return shell_exec('/bin/uname -r');
    }

    /**
     * @inheritdoc
     */
    public function getTotalSwap()
    {
        $meminfo = $this->getMemInfo();
        return array_key_exists('SwapTotal', $meminfo) ? intval($meminfo['SwapTotal']) * 1024 : null;
    }

    /**
     * @inheritdoc
     */
    public function getFreeSwap()
    {
        $memInfo = $this->getMemInfo();
        return array_key_exists('SwapFree', $memInfo) ? intval($memInfo['SwapFree']) * 1024 : null;
    }

    /**
     * @return int|null
     */
    public function getUsedSwap()
    {
        return $this->getTotalSwap() - $this->getFreeSwap();
    }

    /**
     * @inheritdoc
     */
    public function getTotalMem()
    {
        $meminfo = $this->getMemInfo();
        return array_key_exists('MemTotal', $meminfo) ? intval($meminfo['MemTotal']) * 1024 : null;
    }

    /**
     * @inheritdoc
     */
    public function getFreeMem()
    {
        $memInfo = $this->getMemInfo();

        $memFree = array_key_exists('MemFree', $memInfo) ? (int) $memInfo['MemFree'] : null;
        $cached  = array_key_exists('Cached', $memInfo) ? (int) $memInfo['Cached'] : null;

        $result = ($memFree ?: null) + ($cached ?: null);

        return $result ? $result * 1024: null;
    }

    /**
     * @inheritdoc
     */
    public function getUsedMem()
    {
        return $this->getTotalMem() - $this->getFreeMem();
    }

    /**
     * @inheritdoc
     */
    public function getOsType()
    {
        return 'Linux';
    }

    /**
     * @inheritdoc
     */
    public function getCpuinfo()
    {
        if (!$this->cpuInfo) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            $cpuInfo = explode("\n", $cpuInfo);
            $values = [];
            foreach ($cpuInfo as $v) {
                $v = array_map('trim', explode(':', $v));
                if (isset($v[0], $v[1])) {
                    $values[$v[0]] = $v[1];
                }
            }
            $this->cpuInfo = $values;
        }
        return $this->cpuInfo;
    }

    /**
     * Get information about CPU using lscpu untility
     * @return array
     */
    public function getCpuinfoByLsCpu()
    {
        if (!$this->cpuInfoByLsCpu) {
            $lscpu = shell_exec('lscpu');
            $lscpu = explode("\n", $lscpu);
            $values = [];
            foreach ($lscpu as $v) {
                $v = array_map('trim', explode(':', $v));
                if (isset($v[0], $v[1])) {
                    $values[$v[0]] = $v[1];
                }
            }
            $this->cpuInfoByLsCpu = $values;
        }
        return $this->cpuInfoByLsCpu;
    }

    /**
     * @inheritdoc
     */
    public function getCpuModel()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('model name', $cu) ? $cu['model name'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuVendor()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('vendor_id', $cu) ? $cu['vendor_id'] : null;
    }

    /**
     * Return number of physical CPUs
     * @return mixed|null
     */
    public function getPhysicalCpus()
    {
        $cu = $this->getCpuinfoByLsCpu();
        return array_key_exists('CPU(s)', $cu) ? $cu['CPU(s)'] : null;
    }
    
    /**
     * @return mixed|null
     */
    public function getCoresPerSocket()
    {
        $cu = $this->getCpuinfoByLsCpu();
        return array_key_exists('Core(s) per socket', $cu) ? $cu['Core(s) per socket'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuCores()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('siblings', $cu) ? $cu['siblings'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuPhysicalCores()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('cpu cores', $cu) ? $cu['cpu cores'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getDiskUsage()
    {
        return $this->getDiskUsageInfo();
    }

    /**
     * @inheritdoc
     */
    public function getDiskTotal()
    {
        $du = $this->getDiskUsageInfo();
        return array_key_exists('-', $du) ? $du['-']['size'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getDiskFree()
    {
        $du = $this->getDiskUsageInfo();
        return array_key_exists('-', $du) ? $du['-']['avail'] : null;
    }
}
