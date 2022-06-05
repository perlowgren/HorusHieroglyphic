<?php

declare(strict_types=1);

/**
 * Horus Hieroglyphic
 *
 * @author Per Löwgren
 * @date Modified: 2016-01-07
 * @date Created: 2013-01-01
 */

namespace Spirangle\Horus\Formatters;

use Spirangle\Horus\Hieroglyphic;
use Spirangle\Horus\HieroglyphicException;

define('HIERO_DEFAULT_FONT_URL',HIERO_DEFAULT_FONT.'/');
define('HIERO_DEFAULT_HEIGHT',30);
define('HIERO_DEFAULT_LINES',false);

class HtmlFormatter extends Hieroglyphic {
    protected string $fontUrl;
    protected int $height;
    protected bool $lines;
    protected string $comment;

    /**
     * @throws HieroglyphicException
     */
    function __construct(string $text,string $font,array $params) {
        parent::__construct($text,$font,$params);
        $this->fontUrl = $params['font-url'] ?? HIERO_DEFAULT_FONT_URL;
        $this->height = $params['height'] ?? HIERO_DEFAULT_HEIGHT;
        $this->lines = $params['lines'] ?? HIERO_DEFAULT_LINES;
        $this->comment = $params['comment'] ?? '';
        if($this->fontUrl) {
            $this->fontUrl = rtrim($this->fontUrl,'/').'/';
        }
    }

    protected function process(): string {
        if(empty($this->nodes)) return '';
        $ind = "  ";
        $flags = 0;
        $margin = 0;
        $height = $this->height;
        $html = '';
        if($this->comment) $html .= "<!-- $this->comment -->\n";
        if($this->lines) $html .= "<hr class=\"hiero\" />";
        $html .= "<ul class=\"hiero hiero-ltr\" style=\"height:{$height}px;\"><!--\n";
        for($i = 0,$n = count($this->nodes); $i<$n; ++$i) {
//			$html .= $this->nodes[$i]->getHTML($ind,$scale,$flags);

            $node = &$this->nodes[$i];
            $scale = $this->height/100;

            if(!($node->type&(HIERO_GLYPH|HIERO_BLANK|HIERO_JUXTAPOSITION))) {
                if(($flags&HIERO_JUXTAPOSITION)) {
                    $ind = substr($ind,0,-1);
                    $html .= "$ind--></ul></li><!--JUXTAPOSITION\n";
                    $flags &= ~HIERO_JUXTAPOSITION;
                }
                if(($flags&HIERO_SUBORDINATE) && !($node->type&HIERO_SUBORDINATE)) {
                    $ind = substr($ind,0,-1);
                    $html .= "$ind--></ul><!--SUBORDINATE\n$ind--><div style=\"clear:both;\"></div></li><!--\n";
                    $flags &= ~HIERO_SUBORDINATE;
                }
            }
//			$html .= "{$node->mdc} (flags:{$flags},width:{$node->width},height:{$node->height})\n";
            $s = $scale*$node->scale;
            $style = '';
            if(($node->type&HIERO_SHOW) && $node->type!=HIERO_GLYPH) {
                if($node->width) $style .= 'width:'.round($node->width*$s).'px;';
                if($node->height) $style .= 'height:'.round($node->height*$s).'px;';
            }
            $m = (!($flags&(HIERO_JUXTAPOSITION|HIERO_SUBORDINATE))? $margin : 0)+$node->margin;
            if($m) $style .= 'margin-top:'.round($m*$scale).'px;';
            if($style) $style = " style=\"$style\"";
            if($node->type===HIERO_GLYPH) {
                if(($node->next&(HIERO_SUBORDINATE|HIERO_JUXTAPOSITION)) && !($flags&$node->next)) {
                    if($node->next==HIERO_SUBORDINATE) {
                        $html .= "$ind--><li><ul class=\"hiero-subordinate\"><!--\n";
                    } elseif($node->next==HIERO_JUXTAPOSITION) {
                        $html .= "$ind--><li><ul class=\"hiero-juxtaposition\"><!--\n";
                    }
                    $flags |= $node->next;
                    $ind .= "  ";
                }
                $url = "$this->fontUrl$node->glyph.png";
                $w = $node->width? ' width="'.intval($node->width*$s).'"' : '';
                $h = $node->height? ' height="'.intval($node->height*$s).'"' : '';
                $html .= "$ind--><li$style><img src=\"$url\" alt=\"$node->glyph\"$w$h /></li><!--\n";
            } else {
                if($node->type==HIERO_BLANK) {
                    $html .= "$ind--><li><div class=\"hiero-blank\"$style></div></li><!--\n";
                } elseif($node->type==HIERO_CARTOUCHE) {
                    $ind = substr($ind,0,-1);
                    if($node->mdc[0]=='<') {
                        $w = intval(30*$scale);
                        $h = intval(100*$scale);
                        $style = " style=\"background-size:auto {$h}px;height:{$h}px;padding:0 0 0 {$w}px;\"";
                        $html .= "$ind--></ul><!--\n".
                                 "$ind--><div class=\"hiero-cartouche-left-1\"><!--\n".
                                 "$ind--><ul class=\"hiero hiero-ltr\"$style><!--\n";
                        $margin = 9;
                        $this->height -= round($margin*2*$scale);
                    } else {
                        $this->height = $height;
                        $margin = 0;
                        $scale = $this->height/100;
                        $w = intval(45*$scale);
                        $h = intval(100*$scale);
                        $style = " style=\"background-size:{$w}px {$h}px;width:{$w}px;height:{$h}px;\"";
                        $html .= "$ind--></ul><!--\n".
                                 "$ind--><div class=\"hiero-cartouche-right-2\"$style></div><!--\n".
                                 "$ind--></div><!--CARTOUCHE\n".
                                 "$ind--><ul class=\"hiero hiero-ltr\" style=\"height:{$h}px;\"><!--\n";
                    }
                    $ind .= "  ";
                }
            }
        }
        $html .= "--></ul>\n";
        if($this->lines) $html .= "<hr class=\"hiero\" />\n";
        return $html;
    }
}
