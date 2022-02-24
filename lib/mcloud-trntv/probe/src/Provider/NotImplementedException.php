<?php

namespace MediaCloud\Vendor\Probe\Provider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class NotImplementedException extends \Exception
{
    protected $message = 'Method is not implemented in this provider';
}
