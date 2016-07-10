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

if (!defined('ABSPATH')) { header('Location: /'); die; }

/**
 * Loads the content of a view (any old php file) from file.
 *
 * @param string $view The full path to the view.
 * @return The contents of the view file.
 */
function get_view($view)
{
    $contents=file_get_contents($view);
    $contents=parse_view($contents);
    return $contents;
}

/**
 * Extracts short tags from the view
 * @param $contents
 * @return string
 */
function parse_view($contents)
{
    $contents=preg_replace('#{%\s*foreach\s*\(\s*(.*)\s*\)\s*%}#','<?php foreach($1):?>',$contents);
    $contents=preg_replace('#{%\s*endforeach\s*%}#','<?php endforeach; ?>',$contents);
    $contents=preg_replace('#{%\s*if\s*\((.*)\)\s*%}#','<?php if ($1): ?>',$contents);
    $contents=preg_replace('#{%\s*else\s*%}#','<?php else: ?>',$contents);
    $contents=preg_replace('#{%\s*elseif\s*\((.*)\)\s*%}#','<?php elseif ($1): ?>',$contents);
    $contents=preg_replace('#{%\s*endif\s*%}#','<?php endif; ?>',$contents);
    $contents=preg_replace("|\{{2}([^}]*)\}{2}|is",'<?php echo $1; ?>',$contents);
    $contents=preg_replace("|\{{2}(.*)\}{2}|is",'<?php echo $1; ?>',$contents); // for closures.

    return $contents;
}

/**
 * Renders a php fragment
 *
 * @param string $fragment The fragment of PHP code to render
 * @param array $data Variables to extract before rendering the fragment.
 */
function render_fragment($fragment, $data)
{
    if ($data!=null)
        extract($data);

    ob_start();
    eval("?>".trim($fragment));
    $result=ob_get_contents();
    ob_end_clean();

    return $result;
}

/**
 * Renders a view
 *
 * @param string $view The full path to the view to render
 * @param array $data The data to pass into the view
 * @return string The rendered view
 */
function render_view($view,$data)
{
    $contents=get_view(ILAB_VIEW_DIR.'/'.$view);
    return render_fragment($contents,$data);
}

/**
 * Renders a snippet
 * @param $snippet
 * @param $data
 */
function render_snippet($snippet,$data)
{
    $snippet=parse_view($snippet);
    return render_fragment($snippet,$data);
}


