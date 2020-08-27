<?php

namespace MediaCloud\Vendor\Probe;

/**
 * Provider Factory
 * @author Eugene Terentev <eugene@terentev.net>
 * @author Semen Kotliarenko <semako.ua@gmail.com>
 */
class ProviderFactory
{
    /**
     * @var
     */
    protected static $provider;

    /**
     * @var array
     */
    public static $providers = [
        'Linux' => '\MediaCloud\Vendor\Probe\Provider\LinuxProvider',
        'Mac' => '\MediaCloud\Vendor\Probe\Provider\MacProvider',
        'Windows' => '\MediaCloud\Vendor\Probe\Provider\WindowsProvider',
    ];

    /**
     * @param array $config
     * @return null|provider\AbstractProvider
     */
    public static function create($config = [])
    {
        if (null === self::$provider) {
            $osType = self::getOsType();
            if (array_key_exists($osType, self::$providers)) {
                self::$provider = new self::$providers[$osType];
                foreach ($config as $k => $v) {
                    self::$provider->{$k} = $v;
                }
            }
        }
        return self::$provider;


    }

    /**
     * @return string
     */
    public static function getOsType()
    {
        $osType = null;
        if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
            $osType = 'Windows';
        } elseif (strtolower(substr(PHP_OS, 0, 6)) === 'darwin') {
            $osType = 'Mac';
        } elseif (stristr(strtolower(PHP_OS), 'bsd')) {
            $osType = 'BSD';
        } elseif (strtolower(substr(PHP_OS, 0, 5)) === 'linux') {
            $osType = 'Linux';
        }
        return $osType;
    }
}