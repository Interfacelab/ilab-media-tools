<?php
namespace ILab\Stem;

if (class_exists('ILab\Stem\View'))
    return;

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

        // parse parent template
        $extendMatches=[];
        if (preg_match('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#',$contents,$extendMatches))
        {
            $template=$extendMatches[1][0];

            $this->parent=new View(ILAB_VIEW_DIR.'/'.$template);

            $contents=preg_replace('#{%\s*extends\s+([/aA-zZ0-9-_.]+)\s*%}#','',$contents);
        }

        // parse content targets
        $contents=preg_replace('#{%\s*content\s+([/aA-zZ0-9-_.]+)\s*%}#',"<php echo $view->getBlock('$1'); ?>",$contents);

        // parse blocks
        $blockMatches=[];
        if (preg_match_all('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*endblock\s*%}#s',$contents,$blockMatches))
        {
            for($i=0; $i<count($blockMatches[1]); $i++)
            {
                $blockName=$blockMatches[1][$i];
                $this->blocks[$blockName]=$this->parseFragment($blockMatches[2][$i]);
            }

            $contents=preg_replace('#{%\s*block\s*([aA-zZ0-9-_]*)\s*%}(.*?){%\s*endblock\s*%}#s','',$contents);
        }

        $this->parsed=$this->parseFragment($contents);
    }

    private function parseFragment($fragment) {
        $fragment=preg_replace('#{%\s*foreach\s*\(\s*(.*)\s*\)\s*%}#','<?php foreach($1):?>',$fragment);
        $fragment=preg_replace('#{%\s*endforeach\s*%}#','<?php endforeach; ?>',$fragment);
        $fragment=preg_replace('#{%\s*if\s*\((.*)\)\s*%}#','<?php if ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*else\s*%}#','<?php else: ?>',$fragment);
        $fragment=preg_replace('#{%\s*elseif\s*\((.*)\)\s*%}#','<?php elseif ($1): ?>',$fragment);
        $fragment=preg_replace('#{%\s*endif\s*%}#','<?php endif; ?>',$fragment);
        $fragment=preg_replace("|\{{2}([^}]*)\}{2}|is",'<?php echo $1; ?>',$fragment);
        $fragment=preg_replace("|\{{2}(.*)\}{2}|is",'<?php echo $1; ?>',$fragment); // for closures.

        return $fragment;
    }

    private function renderFragment($fragment) {
        if ($this->currentData!=null)
            extract($this->currentData);

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
        $view=new View(ILAB_VIEW_DIR.'/'.$view);
        return $view->render($data);
    }
}
