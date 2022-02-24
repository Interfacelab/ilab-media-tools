<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * MacOS information provider
 * @author Eugene Terentev <eugene@terentev.net>
 * @author Semen Kotliarenko <semako.ua@gmail.com>
 */
class MacProvider extends AbstractBsdProvider
{
    public function getOsType()
    {
        return 'Mac';
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
