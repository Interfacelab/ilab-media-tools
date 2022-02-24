<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * Windows information provider
 * @author Eugene Terentev <eugene@terentev.net>
 */
class WindowsProvider extends AbstractProvider
{
    /**
     * @var string
     */
    public $wmiHost;
    /**
     * @var string
     */
    public $wmiUsername;
    /**
     * @var string
     */
    public $wmiPassword;

    /**
     * @var \COM
     */
    protected $wmiConnection;

    /**
     * @var \VARIANT
     */
    protected $cpuInfo;

    /**
     * @return mixed
     */
    public function getOsRelease()
    {
        $objSet = $this->getWMI()->ExecQuery("SELECT Name FROM Win32_OperatingSystem");
        foreach ($objSet as $obj) {
            return $obj->name;
        }
    }

    /**
     * @return string
     */
    public function getOsType()
    {
        return 'Windows';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getArchitecture()
    {
        $objSet = $this->getWMI()->ExecQuery("SELECT Architecture FROM Win32_Processor");
        foreach ($objSet as $obj) {
            switch ($obj->Architecture) {
                case 0:
                    return "x86";
                case 1:
                    return "MIPS";
                case 2:
                    return "Alpha";
                case 3:
                    return "PowerPC";
                case 6:
                    return "Itanium-based systems";
                case 9:
                    return "x64";
            }
        }
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
        foreach ($hosts as $host) {
            $command = "ping -n {$count} {$host}";
            $result = exec($command);
            $matches = [];
            preg_match('/average\s?=\s?(\d+)/sim', $result, $matches);
            $ping[$host] = $matches[1];
        }
        return $ping;
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getUptime()
    {
        $objSet = $this->getWMI()->ExecQuery("SELECT SystemUpTime FROM Win32_PerfFormattedData_PerfOS_System");
        foreach ($objSet as $obj) {
            return $obj->SystemUpTime;
        }
    }

    /**
     * @return mixed string|array
     * @throws \Exception
     */
    public function getLoadAverage()
    {
        $load = [];
        foreach ($this->getCpuInfo() as $cpu) {
            $load[] = $cpu->LoadPercentage;
        }

        return round(array_sum($load) / count($load), 2);
    }

    /**
     * @inheritdoc
     */
    public function getCpuCores()
    {
        $cpuInfo = $this->getCpuInfo();
        foreach ($cpuInfo as $obj) {
            return $obj->NumberOfLogicalProcessors;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCpuPhysicalCores()
    {
        $cpuInfo = $this->getCpuInfo();
        foreach ($cpuInfo as $obj) {
            return $obj->NumberOfCores;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCpuModel()
    {
        $cpuInfo = $this->getCpuInfo();
        foreach ($cpuInfo as $obj) {
            return $obj->Name;
        }
    }

    /**
     * @return mixed
     */
    public function getCpuVendor()
    {
        $cpuInfo = $this->getCpuInfo();
        foreach ($cpuInfo as $obj) {
            return $obj->Manufacturer;
        }
    }

    /**
     * @return \VARIANT
     */
    public function getCpuInfo()
    {
        if ($this->cpuInfo === null) {
            $this->cpuInfo = $this->getWMI()->ExecQuery("SELECT * FROM Win32_Processor");
        }
        return $this->cpuInfo;
    }

    /**
     * @return bool|int
     * @throws \Exception
     */
    public function getTotalMem()
    {
        $totalMemory = 0;
        $objSet = $this->getWMI()->ExecQuery("SELECT TotalPhysicalMemory FROM Win32_ComputerSystem");
        foreach ($objSet as $obj) {
            $totalMemory = $obj->TotalPhysicalMemory;
            break;
        }

        return $totalMemory;
    }

    /**
     * @return bool|int
     * @throws \Exception
     */
    public function getFreeMem()
    {
        $freeMemory = 0;
        $objSet = $this->getWMI()->ExecQuery("SELECT FreePhysicalMemory FROM Win32_OperatingSystem");
        foreach ($objSet as $obj) {
            $freeMemory += $obj->FreePhysicalMemory;
        }
        return $freeMemory;
    }

    /**
     * @return int
     */
    public function getTotalSwap()
    {
        $total = 0;
        $objSet = $this->getWMI()->ExecQuery("SELECT AllocatedBaseSize FROM Win32_PageFileUsage");
        foreach ($objSet as $device) {
            $total += $device->AllocatedBaseSize;
        }
        return $total;
    }

    /**
     * @return int
     */
    public function getUsedSwap()
    {
        $used = 0;
        $objSet = $this->getWMI()->ExecQuery("SELECT CurrentUsage FROM Win32_PageFileUsage");
        foreach ($objSet as $device) {
            $used += $device->CurrentUsage;
        }
        return $used;
    }

    /**
     * @return int
     */
    public function getFreeSwap()
    {
        return $this->getTotalSwap() - $this->getUsedSwap();
    }

    /**
     * @return array
     */
    public function getCpuUsage()
    {
        $load = [];
        $objSet = $this->getWMI()->ExecQuery("SELECT LoadPercentage FROM Win32_Processor");
        foreach ($objSet as $obj) {
            $load[] = $obj->LoadPercentage / 100;
        }

        return $load;
    }

    /**
     * @return mixed
     */
    public function getOsKernelVersion()
    {
        $wmi = $this->getWMI();
        $objSet = $wmi->ExecQuery("SELECT BuildNumber FROM Win32_OperatingSystem");
        foreach ($objSet as $obj) {
            return $obj->BuildNumber;
        }
    }

    /**
     * @return \COM
     */
    protected function getWMI()
    {
        if ($this->wmiConnection === null) {
            $wmiLocator = new \COM('WbemScripting.SWbemLocator');
            try {
                $this->wmiConnection = $wmiLocator->ConnectServer(
                    $this->wmiHost,
                    'root\CIMV2',
                    $this->wmiUsername,
                    $this->wmiPassword
                );
                $this->wmiConnection->Security_->impersonationLevel = 3;
            } catch (\Exception $e) {
                if ($e->getCode() == '-2147352567') {
                    $this->wmiConnection = $wmiLocator->ConnectServer($this->wmiHosthost, 'root\CIMV2', null, null);
                    $this->wmiConnection->Security_->impersonationLevel = 3;
                }
            }
        }
        return $this->wmiConnection;
    }

    /**
     * @return mixed
     */
    public function getUsedMem()
    {
        return $this->getTotalMem() - $this->getFreeMem();
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
    public function getDiskUsage()
    {
        throw new NotImplementedException;
    }

    /**
     * @inheritdoc
     */
    public function getDiskTotal()
    {
        throw new NotImplementedException;
    }

    /**
     * @inheritdoc
     */
    public function getDiskFree()
    {
        throw new NotImplementedException;
    }
}
