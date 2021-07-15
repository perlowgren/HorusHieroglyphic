<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/** Horus Hieroglyphic
 * 
 * @file api/Horus/HorusHiero.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-07
 * @date Created: 2013-01-01
 */

namespace Horus;

use Horus\HorusNode;
use Horus\HorusHiero;

define('HIERO_DEFAULT_FONT_URL', HIERO_DEFAULT_FONT.'/');  //!< 
define('HIERO_DEFAULT_HEIGHT',   30);                      //!< 
define('HIERO_DEFAULT_LINES',    false);                   //!< 

class HorusHTML extends HorusHiero {
	protected $font_url;  //!< 
	protected $height;    //!< 
	protected $lines;     //!< 
	protected $comment;   //!< 

	/** 
	 * @param $font Name of font to use
	 * @param $text Text to format
	 * @param $format Format of text (only Manuel de Codage available at the moment)
	 */
	function __construct($text,$font,$format,$params) {
		parent::__construct($text,$font,$format,$params);
		$this->font_url  = isset($params['font-url'])?  $params['font-url'] : HIERO_DEFAULT_FONT_URL;
		$this->height    = isset($params['height'])?    $params['height']   : HIERO_DEFAULT_HEIGHT;
		$this->lines     = isset($params['lines'])?     $params['lines']    : HIERO_DEFAULT_LINES;
		$this->comment   = isset($params['comment'])?   $params['comment']  : false;
		if($this->font_url)
			$this->font_url = rtrim($this->font_url,'/').'/';
	}

	protected function process() {
		if($this->nodes===false) return '';
		$ind="\t";
		$flags = 0;
		$margin = 0;
		$height = $this->height;
		$html = '';
		if($this->comment) $html .= "<!-- {$this->comment} -->\n";
		if($this->lines) $html .= "<hr class=\"hiero\" />";
		$html .= "<ul class=\"hiero hiero-ltr\" style=\"height:{$height}px;\"><!--\n";
		for($i=0,$n=count($this->nodes); $i<$n; ++$i) {
//			$html .= $this->nodes[$i]->getHTML($ind,$scale,$flags);

			$node = &$this->nodes[$i];
			$scale = $this->height/100;

			if(($flags&HIERO_JUXTAPOSITION) && !($node->type&(HIERO_GLYPH|HIERO_BLANK|HIERO_JUXTAPOSITION))) {
				$ind = substr($ind,0,-1);
				$html .= "{$ind}--></ul></li><!--JUXTAPOSITION\n";
				$flags &= ~HIERO_JUXTAPOSITION;
			}
			if(($flags&HIERO_SUBORDINATE) && !($node->type&(HIERO_GLYPH|HIERO_BLANK|HIERO_JUXTAPOSITION|HIERO_SUBORDINATE))) {
				$ind = substr($ind,0,-1);
				$html .= "{$ind}--></ul><!--SUBORDINATE\n{$ind}--><div style=\"clear:both;\"></div></li><!--\n";
				$flags &= ~HIERO_SUBORDINATE;
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
			if($style) $style = ' style="'.$style.'"';
			if($node->type==HIERO_GLYPH) {
				if(($node->next&(HIERO_SUBORDINATE|HIERO_JUXTAPOSITION)) && !($flags&$node->next)) {
					if($node->next==HIERO_SUBORDINATE) $html .= "{$ind}--><li><ul class=\"hiero-subordinate\"><!--\n";
					elseif($node->next==HIERO_JUXTAPOSITION) $html .= "{$ind}--><li><ul class=\"hiero-juxtaposition\"><!--\n";
					$flags |= $node->next;
					$ind .= "\t";
				}
				$w = $node->width? ' width="'.intval($node->width*$s).'"' : '';
				$h = $node->height? ' height="'.intval($node->height*$s).'"' : '';
				$html .= "{$ind}--><li{$style}><img src=\"{$this->font_url}{$node->glyph}.png\"{$w}{$h} /></li><!--\n";
			} else{
				if($node->type==HIERO_BLANK) {
					$html .= "{$ind}--><li><div class=\"hiero-blank\"{$style}></div></li><!--\n";
				} elseif($node->type==HIERO_CARTOUCHE) {
					$ind = substr($ind,0,-1);
					if($node->mdc[0]=='<') {
						$w = intval(30*$scale);
						$h = intval(100*$scale);
						$html .= "{$ind}--></ul><!--\n";
						$html .= "{$ind}--><div class=\"hiero-cartouche-left-1\"><!--\n";
						$html .= "{$ind}--><ul class=\"hiero hiero-ltr\" style=\"background-size:auto {$h}px;height:{$h}px;padding:0 0 0 {$w}px;\"><!--\n";
						$margin = 9;
						$this->height -= round($margin*2*$scale);
					} else {
						$this->height = $height;
						$margin = 0;
						$scale = $this->height/100;
						$w = intval(45*$scale);
						$h = intval(100*$scale);
						$html .= "{$ind}--></ul><!--\n";
						$html .= "{$ind}--><div class=\"hiero-cartouche-right-2\" style=\"background-size:{$w}px {$h}px;width:{$w}px;height:{$h}px;\"></div><!--\n";
						$html .= "{$ind}--></div><!--CARTOUCHE\n";
						$html .= "{$ind}--><ul class=\"hiero hiero-ltr\" style=\"height:{$h}px;\"><!--\n";
					}
					$ind .= "\t";
				}
			}


		}
		$html .= "--></ul>\n";
		if($this->lines) $html .= "<hr class=\"hiero\" />\n";
		return $html;
	}
}


