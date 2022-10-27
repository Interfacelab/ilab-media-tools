<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class AbstractUnixProvider extends AbstractProvider
{
    /**
     * @var array|null
     */
    protected $memInfo;
    /**
     * @var array|null
     */
    protected $sysctlInfo;
    /**
     * @var array|null
     */
    protected $diskUsageInfo;

    /**
     * @param int $interval
     * @return array
     */
    public function getCpuUsage($interval = 1)
    {
        $stat = function () {
            $stat = file_get_contents('/proc/stat');
            $stat = explode("\n", $stat);
            $result = [];
            foreach ($stat as $v) {
                $v = explode(' ', $v);
                if (isset($v[0])
                    && strpos(strtolower($v[0]), 'cpu') === 0
                    && preg_match('/cpu[\d]/sim', $v[0])
                ) {
                    $result[] = array_slice($v, 1, 4);
                }

            }
            return $result;
        };
        $stat1 = $stat();
        usleep($interval * 1000000);
        $stat2 = $stat();
        $usage = [];
        for ($i = 0; $i < $this->getCpuCores(); $i++) {
            if (isset($stat1[$i]) && isset($stat2[$i])) {
                $total = array_sum($stat2[$i]) - array_sum($stat1[$i]);
                $idle = $stat2[$i][3] - $stat1[$i][3];
                $usage[$i] = $total !== 0 ? ($total - $idle) / $total : 0;
            }
        }
        return $usage;
    }

    /**
     * @return array
     */
    public function getSysctlInfo()
    {
        if (null === $this->sysctlInfo) {
            $sysctlbin = $this->getSysctlPath();
            $data = explode(PHP_EOL, shell_exec("{$sysctlbin} -A"));
            $this->sysctlInfo = [];
            foreach ($data as $line) {
                $line = explode(':', $line);
                if (isset($line[0], $line[1])) {
                    $this->sysctlInfo[$line[0]] = trim($line[1]);
                }
            }
        }
        return $this->sysctlInfo;
    }

    /**
     * @return string
     */
    public function getSysctlPath()
    {
        $paths = explode(':', getenv('PATH'));
        foreach ($paths as $path) {
            $abs = $path . DIRECTORY_SEPARATOR . 'sysctl';
            if (file_exists($abs)) {
                return $abs;
            }
        }

        return 'sysctl';
    }

    /**
     * @return array|null
     */
    public function getMemInfo()
    {
        if (null === $this->memInfo) {
            $data = explode("\n", file_get_contents('/proc/meminfo'));
            $this->memInfo = [];
            foreach ($data as $line) {
                $line = explode(':', $line);
                if (isset($line[0], $line[1])) {
                    $this->memInfo[$line[0]] = trim($line[1]);
                }
            }
        }
        return $this->memInfo;
    }

    /**
     * @param string $interval
     * @return mixed
     */
    public function getLoadAverage($interval = '5')
    {
        $la = array_combine(['1','5','15'], sys_getloadavg());
        if (array_key_exists($interval, $la)) {
            return $la[$interval];
        } else {
            throw new \InvalidArgumentException;
        }
    }

    /**
    * @return $mixed
    */
    public function getDiskUsageInfo()
    {
        if(null === $this->diskUsageInfo) {
            $data = explode(PHP_EOL, shell_exec('df -h --total|awk \'{print $1" "$2" "$3" "$4" "$5" "$6}\''));
            $this->diskUsageInfo = [];
            foreach ($data as $row => $line) {
                if($row == 0 || !$line){
                    continue;
                }

                list($filesystem, $size, $used, $avail, $usepercentage, $mountedon) = explode(" ", $line);
                if (isset($filesystem, $size, $used, $avail, $usepercentage, $mountedon)) {
                    $this->diskUsageInfo[$mountedon] = ['filesystem' => $filesystem, 'size' => $size, 'used' => $used, 'avail' => $avail, 'usepercentage' => $usepercentage, 'mountedon' => $mountedon];
                }
            }
        }
        return $this->diskUsageInfo;
    }
}
