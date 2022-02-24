<?php
use MediaCloud\Vendor\Google\ApiCore\Testing\MessageAwareArrayComparator;
use MediaCloud\Vendor\Google\ApiCore\Testing\ProtobufMessageComparator;
use MediaCloud\Vendor\Google\ApiCore\Testing\ProtobufGPBEmptyComparator;

date_default_timezone_set('UTC');
\SebastianBergmann\Comparator\Factory::getInstance()->register(new MessageAwareArrayComparator());
\SebastianBergmann\Comparator\Factory::getInstance()->register(new ProtobufMessageComparator());
\SebastianBergmann\Comparator\Factory::getInstance()->register(new ProtobufGPBEmptyComparator());

PHPUnit_Framework_Error_Deprecated::$enabled = false;
