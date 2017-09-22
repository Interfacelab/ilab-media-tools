<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Utilities;

if (!defined('ABSPATH')) { header('Location: /'); die; }

class View {
    protected $currentBlocks;
    protected $currentData;

    protected $blocks;
    protected $parent;
    protected $parsed;

    public function __construct($view) {
        $this->currentBlocks=[];
        $this->currentData=[];
        $this->blocks=[];
        $this->parent=null;
        $this->parse($view);
    }

    public function parse($view) {
        $contents=file_get_contents($view);

        $includeMatches=[];
        if (preg_match_all('#{%\s*include\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$includeMatches,PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))
        {
            for($i=count($includeMatches[0])-1; $i>=0; $i--)
            {
                $included=file_get_contents(ILAB_VIEW_DIR.'/'.$includeMatches[1][$i][0]);
                $contents=substr_replace($contents,$included,$includeMatches[0][$i][1],strlen($includeMatches[0][$i][0]));
//                $contents=str_replace($includeMatches[0][$i],$included,$contents);
            }
        }


        // parse parent template
        $extendMatches=[];
        if (preg_match('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$extendMatches))
        {
            $template=$extendMatches[1];

            $this->parent=new View( ILAB_VIEW_DIR . '/' . $template);

            $contents=preg_replace('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#','',$contents);
        }

        // parse content targets
        $contents=preg_replace('#{%\s*content\s+([/aA-zZ0-9-_.]+)\s*%}#','<?php echo $view->getBlock("$1"); ?>',$contents);

        // parse blocks
        $blockMatches=[];
        if (preg_match_all('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*end\s*block\s*%}#s',$contents,$blockMatches))
        {
            for($i=0; $i<count($blockMatches[1]); $i++)
            {
                $blockName=$blockMatches[1][$i];
                $this->blocks[$blockName]=$this->parseFragment($blockMatches[2][$i]);
            }

            $contents=preg_replace('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*end\s*block\s*%}#s','',$contents);
        }

        $this->parsed=$this->parseFragment($contents);
    }

    private function parseFragment($fragment) {
        $fragment=preg_replace('#{%\s*for\s*each\s*\(\s*(.*)\s*\)\s*%}#','<?php foreach($1):?>',$fragment);
        $fragment=preg_replace('#{%\s*end\s*for\s*each\s*%}#','<?php endforeach; ?>',$fragment);
        $fragment=preg_replace('#{%\s*if\s*\((.*)\)\s*%}#','<?php if ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*else\s*%}#','<?php else: ?>',$fragment);
        $fragment=preg_replace('#{%\s*else\s*if\s*\((.*)\)\s*%}#','<?php elseif ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*end\s*if\s*%}#','<?php endif; ?>',$fragment);
        $fragment=preg_replace("|\{{2}([^}]*)\}{2}|is",'<?php echo $1; ?>',$fragment);
        $fragment=preg_replace("|\{{2}(.*)\}{2}|is",'<?php echo $1; ?>',$fragment); // for closures.

        return $fragment;
    }

    private function renderFragment($fragment) {
        $data=($this->currentData!=null) ? $this->currentData : [];

        $data['view']=$this;
        extract($data);

        ob_start();
        eval("?>".trim($fragment));
        $result=ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public function getBlock($blockId) {
        if (!isset($this->currentBlocks[$blockId]))
            return '';

        return $this->renderFragment($this->currentBlocks[$blockId]);
    }

    public function render($data,$blocks=null) {
        $allBlocks=$this->blocks;

        if ($blocks)
            $allBlocks=array_merge($allBlocks,$blocks);


        if ($this->parent)
            return $this->parent->render($data,$allBlocks);

        $this->currentBlocks=$allBlocks;
        $this->currentData=$data;

        return $this->renderFragment($this->parsed);
    }

    public static function render_view($view, $data) {
        $view=new View( ILAB_VIEW_DIR . '/' . $view);
        return $view->render($data);
    }
}
