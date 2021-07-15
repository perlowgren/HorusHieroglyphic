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
 * @file api/Horus/HorusNode.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-07
 * @date Created: 2013-01-01
 */

namespace Horus;

use Horus\HorusNode;
use Horus\HorusHiero;

class HorusNode {
	private $horus;         //!< 
	public $index;          //!< 
	public $mdc;            //!< 
	public $glyph;          //!< 
	public $width;          //!< 
	public $height;         //!< 
	public $margin;         //!< 
	public $scale;          //!< 
	public $type;           //!< 
	public $next;           //!< 

	function __construct(&$horus,$i,$c) {
		$this->horus = &$horus;
		$this->index = $i;
		$this->type = HIERO_NONE;
		$this->next = HIERO_NONE;
		$this->height = 0;
		$this->margin = 0;
		$this->scale = 1.0;
		if(strpos("\t\n  !!..__-:*()|<0<1<2<3<h0<h1<h2<h3h3>h2>h1>h0>",$c)!==false) {
			if(strpos("\t\n  __-",$c)!==false) $this->type = HIERO_SEPARATOR;
			elseif($c=='.' || $c=='..') $this->type = HIERO_BLANK;
			elseif($c==':') $this->type = HIERO_SUBORDINATE;
			elseif($c=='*') $this->type = HIERO_JUXTAPOSITION;
			elseif($c=='(' || $c==')') $this->type = HIERO_CLUSTER;
			elseif($c=='!' || $c=='!!') $this->type = HIERO_END;
			elseif($c=='|') $this->type = HIERO_TEXT;
			else $this->type = HIERO_CARTOUCHE;
			if($c=='.') $this->height = 50;
			else $this->height = ($this->type&HIERO_SHOW)? 100 : 0;
			if($this->type==HIERO_BLANK) $this->width = $this->height;
		} else {
			$phonemes = $horus->getPhonemes();
			$metrics = $horus->getMetrics();
			$this->glyph = isset($phonemes[$c])? $phonemes[$c] : $c;
			if(isset($metrics[$this->glyph])) {
				$m = $metrics[$this->glyph];
				$this->width = $m['w'];
				$this->height = $m['h'];
				$this->type = HIERO_GLYPH;
			}
		}
		if($this->height>100) $this->scale = 100/$this->height;
		$this->mdc = $c;
	}

	public function rescale() {
		$h = $this->getBlockHeight($n,$rows,0);
		if($h>0 && $h!=100) {
			$nodes = &$this->horus->getNodes();
			for($i=$this->index,$s=100/$h,$m=(100-$h)/($rows+1); $i<=$n; ++$i) {
				if($h>100) $nodes[$i]->scale = $s;
				elseif($h<100) $nodes[$i]->margin = $m;
			}
		}
		return $n+1;
	}

	public function getBlockHeight(&$i,&$rows,$padding=0) {
		$i = $this->index;
		$rows = 1;
		$h = $this->height;
		if($h>100) $h = 100;
		$nodes = &$this->horus->getNodes();
		if($this->type==HIERO_GLYPH && ($this->next&HIERO_BLOCK)) {
			for($h=0,$r=0,$max=0,$n=count($nodes); $i<$n; ++$i) {
				$p = &$nodes[$i];
				$t = $p->type;
//echo "getBlockHeight(mdc={$p->mdc},height={$p->height},h={$h},max={$max})<br />\n";
				if($t==HIERO_GLYPH || $t==HIERO_BLANK) $r = $p->height;
				elseif($t==HIERO_SUBORDINATE) { $h += $max;$r = 0;$max = 0;++$rows; }
				elseif($t==HIERO_CLUSTER) {
					if($p->mdc=='(') $r = $nodes[$i+1]->getBlockHeight($i,$x,$padding);
					else break;
				}
				elseif($t!=HIERO_JUXTAPOSITION) break;
				if($r>$max) $max = $r;
			}
			$h += $max;
		}
//echo "getBlockHeight(index={$this->index}/{$i},height={$h})<br />\n";
		return $h;
	}
}


