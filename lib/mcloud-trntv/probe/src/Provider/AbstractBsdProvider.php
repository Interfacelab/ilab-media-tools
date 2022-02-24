<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 * @author Semen Kotliarenko <semako.ua@gmail.com>
 */
abstract class AbstractBsdProvider extends AbstractUnixProvider
{
    /**
     * @var array|null
     */
    private $cpuInfo;

    /**
     */
    public function getTotalSwap()
    {
        $meminfo = $this->getMemInfo();
        return array_key_exists('SwapTotal', $meminfo) ? (int) ($meminfo['SwapTotal'] * 1024) : null;
    }

    /**
     * @inheritdoc
     */
    public function getTotalMem()
    {
        $meminfo = $this->getMemInfo();
        return array_key_exists('MemTotal', $meminfo) ? (int) ($meminfo['MemTotal'] * 1024) : null;
    }

    /**
     * @inheritdoc
     */
    public function getFreeMem()
    {
        $memInfo = $this->getMemInfo();

        $pageSize  = array_key_exists('hw.pagesize', $memInfo) ? (int) $memInfo['hw.pagesize'] : 4096;
        $pagesFree = array_key_exists('Pages free', $memInfo) ? (int) $memInfo['Pages free'] : null;
        $result    = $pageSize * $pagesFree;

        return $result ? $result: null;
    }

    /**
     * @inheritdoc
     */
    public function getOsRelease()
    {
        return shell_exec('sw_vers -productVersion');
    }

    /**
     * @inheritdoc
     */
    public function getOsKernelVersion()
    {
        return shell_exec('uname -r');
    }

    /**
     * @inheritdoc
     */
    public function getCpuModel()
    {
        $sysctlinfo = $this->getSysctlInfo();
        return array_key_exists('machdep.cpu.brand_string', $sysctlinfo)
            ? $sysctlinfo['machdep.cpu.brand_string']
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuCores()
    {
        $sysctlinfo = $this->getSysctlInfo();
        return array_key_exists('hw.physicalcpu', $sysctlinfo)
            ? $sysctlinfo['hw.physicalcpu']
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuVendor()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('machdep.cpu.vendor', $cu) ? $cu['machdep.cpu.vendor'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getPhysicalCpus()
    {
        throw new NotImplementedException;
    }

    /**
     * @inheritdoc
     */
    public function getCpuPhysicalCores()
    {
        $cu = $this->getCpuinfo();
        return array_key_exists('hw.physicalcpu', $cu) ? $cu['hw.physicalcpu'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getCpuUsage($interval = 1)
    {
        $top = array_reverse(explode("\n", shell_exec('top -l 1 -n 1')));
        $found = null;

        foreach ($top as $item) {
            if ($found) {
                continue;
            }

            if (preg_match('/CPU usage/i', $item)) {
                $found = $item;
            }
        }

        $usage = [];
        if (preg_match_all('/[0-9\.]+/i', $found, $matches)) {
            $usage[] = ($matches[0][0] + $matches[0][1]) / 100;
        }
        return $usage;
    }

    /**
     * @inheritdoc
     */
    public function getUptime()
    {
        $sysctl = $this->getSysctlInfo();
        return (int)substr($sysctl['kern.boottime'], 8, 10);
    }

    /**
     * @inheritdoc
     */
    public function getFreeSwap()
    {
        $meminfo = $this->getMemInfo();
        return array_key_exists('SwapFree', $meminfo) ? (int) ($meminfo['SwapFree'] * 1024) : null;
    }

    /**
     * @inheritdoc
     */
    public function getUsedMem()
    {
        return $this->getTotalMem() - $this->getFreeMem();
    }

    public function getUsedSwap()
    {
        return $this->getTotalSwap() - $this->getFreeSwap();
    }

    /**
     * @inheritdoc
     */
    public function getMemInfo()
    {
        if (null === $this->memInfo) {
            $vmstat = explode("\n", shell_exec('vm_stat'));
            $this->memInfo = [];
            foreach ($vmstat as $line) {
                $line = explode(':', $line);
                if (isset($line[0], $line[1])) {
                    $key = str_replace('"', '', $line[0]);
                    $val = preg_replace('/\.$/', '', trim(str_replace('"', '', $line[1])));
                    $this->memInfo[$key] = $val;
                }
            }

            $sysctl = $this->getSysctlInfo();
            $this->memInfo['MemTotal'] = array_key_exists('hw.memsize', $sysctl) ? intval($sysctl['hw.memsize'] / 1024) : null;

            if (array_key_exists('vm.swapusage', $sysctl)) {
                $tmp = explode('  ', $sysctl['vm.swapusage']);
                foreach ($tmp as $item) {
                    $item = explode(' = ', $item);

                    if (sizeof($item) != 2) {
                        continue;
                    }

                    switch ($item[0]) {
                        case 'total':
                            $this->memInfo['SwapTotal'] = intval($item[1]) * 1024;
                            break;

                        case 'free':
                            $this->memInfo['SwapFree'] = intval($item[1]) * 1024;
                            break;
                    }
                }
            }
        }
        return $this->memInfo;
    }

    /**
     * @inheritdoc
     */
    public function getCpuInfo()
    {
        if (!$this->cpuInfo) {
            $data = $this->getSysctlInfo();
            $values = [];
            foreach ($data as $k => $v) {
                switch ($k) {
                    case 'machdep.cpu.brand_string':
                    case 'machdep.cpu.vendor':
                    case 'hw.logicalcpu':
                    case 'hw.physicalcpu':
                        $values[$k] = $v;
                        break;
                }
            }
            $this->cpuInfo = $values;
        }
        return $this->cpuInfo;
    }
}
