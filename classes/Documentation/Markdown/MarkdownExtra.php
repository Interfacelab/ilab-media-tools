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

namespace ILAB\MediaCloud\Documentation\Markdown;

class MarkdownExtra extends \Michelf\MarkdownExtra {
	public $code_class_prefix = 'language-';

    /**
     * Calback for processing tables
     * @param  array $matches
     * @return string
     */
    protected function _doTable_callback($matches) {
        $head		= $matches[1];
        $underline	= $matches[2];
        $content	= $matches[3];

        // Remove any tailing pipes for each line.
        $head		= preg_replace('/[|] *$/m', '', $head);
        $underline	= preg_replace('/[|] *$/m', '', $underline);
        $content	= preg_replace('/[|] *$/m', '', $content);

        // Reading alignement from header underline.
        $separators	= preg_split('/ *[|] */', $underline);
        foreach ($separators as $n => $s) {
            if (preg_match('/^ *-+: *$/', $s))
                $attr[$n] = $this->_doTable_makeAlignAttr('right');
            else if (preg_match('/^ *:-+: *$/', $s))
                $attr[$n] = $this->_doTable_makeAlignAttr('center');
            else if (preg_match('/^ *:-+ *$/', $s))
                $attr[$n] = $this->_doTable_makeAlignAttr('left');
            else
                $attr[$n] = '';
        }

        // Parsing span elements, including code spans, character escapes,
        // and inline HTML tags, so that pipes inside those gets ignored.
        $head		= $this->parseSpan($head);
        $headers	= preg_split('/ *[|] */', $head);
        $col_count	= count($headers);
        $attr       = array_pad($attr, $col_count, '');

        foreach($headers as $n => $headerText) {
            if (preg_match("/(.*){([0-9.]+(?:px|em|rem|vw|vh|%))}/", $headerText, $classMatches)) {
                $attr[$n] .= " style='width:{$classMatches[2]}; max-width:{$classMatches[2]}'";
                $headers[$n] = $classMatches[1];
            } else if (preg_match("/(.*){([aA-zZ0-9-_ ]+)}/", $headerText, $classMatches)) {
                $attr[$n] .= " class='{$classMatches[2]}'";
                $headers[$n] = $classMatches[1];
            }
        }

        // Write column headers.
        $text = "<table>\n";
        $text .= "<thead>\n";
        $text .= "<tr>\n";
        foreach ($headers as $n => $header)
            $text .= "  <th$attr[$n]>" . $this->runSpanGamut(trim($header)) . "</th>\n";
        $text .= "</tr>\n";
        $text .= "</thead>\n";

        // Split content by row.
        $rows = explode("\n", trim($content, "\n"));

        $text .= "<tbody>\n";
        foreach ($rows as $row) {
            // Parsing span elements, including code spans, character escapes,
            // and inline HTML tags, so that pipes inside those gets ignored.
            $row = $this->parseSpan($row);

            // Split row by cell.
            $row_cells = preg_split('/ *[|] */', $row, $col_count);
            $row_cells = array_pad($row_cells, $col_count, '');

            $text .= "<tr>\n";
            foreach ($row_cells as $n => $cell)
                $text .= "  <td$attr[$n]>" . $this->runSpanGamut(trim($cell)) . "</td>\n";
            $text .= "</tr>\n";
        }
        $text .= "</tbody>\n";
        $text .= "</table>";

        return $this->hashBlock($text) . "\n";
    }
}