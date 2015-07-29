<?php
class ILabMediaTools
{
    protected $tools;

    public function __construct()
    {
        $toolList=json_decode(file_get_contents(ILAB_TOOLS_DIR.'/tools.json'),true);

        $this->tools=[];

        foreach($toolList as $toolName => $toolInfo)
        {
            require_once(ILAB_CLASSES_DIR."/tools/$toolName/".$toolInfo['tool']['file']);
            $className=$toolInfo['tool']['class'];
            $this->tools[]=new $className();
        }
    }
}