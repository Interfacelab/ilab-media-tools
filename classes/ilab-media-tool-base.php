<?php
abstract class ILabMediaToolBase {

    protected $toolManager;

    public function __construct($toolManager)
    {
        $this->toolManager=$toolManager;
    }
}