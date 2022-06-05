<?php

declare(strict_types=1);

/**
 * Horus Hieroglyphic
 *
 * @author Per Löwgren
 * @date Modified: 2016-01-07
 * @date Created: 2013-01-01
 */

namespace Spirangle\Horus;

class HieroglyphicNode {
    private Hieroglyphic $hiero;
    public int $index;
    public string $mdc;
    public string $glyph;
    public int $width;
    public int $height;
    public int $margin;
    public float $scale;
    public int $type;
    public int $next;

    function __construct(Hieroglyphic $hiero,int $i,string $c) {
        $this->hiero = $hiero;
        $this->index = $i;
        $this->type = HIERO_NONE;
        $this->next = HIERO_NONE;
        $this->height = 0;
        $this->margin = 0;
        $this->scale = 1.0;
        if(strpos("\t\n  !!..__-:*()|<0<1<2<3<h0<h1<h2<h3h3>h2>h1>h0>",$c)!==false) {
            if(strpos("\t\n  __-",$c)!==false) $this->type = HIERO_SEPARATOR;
            elseif($c==='.' || $c==='..') $this->type = HIERO_BLANK;
            elseif($c===':') $this->type = HIERO_SUBORDINATE;
            elseif($c==='*') $this->type = HIERO_JUXTAPOSITION;
            elseif($c==='(' || $c===')') $this->type = HIERO_CLUSTER;
            elseif($c==='!' || $c==='!!') $this->type = HIERO_END;
            elseif($c==='|') $this->type = HIERO_TEXT;
            else $this->type = HIERO_CARTOUCHE;
            if($c==='.') $this->height = 50;
            else $this->height = ($this->type&HIERO_SHOW)? 100 : 0;
            if($this->type===HIERO_BLANK) $this->width = $this->height;
        } else {
            $phonemes = $hiero->getPhonemes();
            $metrics = $hiero->getMetrics();
            $this->glyph = $phonemes[$c] ?? $c;
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

    public function rescale(): int {
        list($n,$rows,$h) = $this->getBlockHeight(0);
        if($h>0 && $h!=100) {
            $nodes = $this->hiero->getNodes();
            for($i = $this->index,$s = 100/$h,$m = (100-$h)/($rows+1); $i<=$n; ++$i) {
                if($h>100) $nodes[$i]->scale = $s;
                elseif($h<100) $nodes[$i]->margin = intval($m);
            }
        }
        return $n+1;
    }

    public function getBlockHeight(int $padding = 0): array {
        $i = $this->index;
        $rows = 1;
        $h = $this->height;
        if($h>100) $h = 100;
        $nodes = $this->hiero->getNodes();
        if($this->type===HIERO_GLYPH && ($this->next&HIERO_BLOCK)) {
            for($h = 0,$r = 0,$max = 0,$n = count($nodes); $i<$n; ++$i) {
                $p = $nodes[$i];
                $t = $p->type;
//echo "getBlockHeight(mdc={$p->mdc},height={$p->height},h={$h},max={$max})<br />\n";
                if($t===HIERO_GLYPH || $t===HIERO_BLANK) $r = $p->height;
                elseif($t===HIERO_SUBORDINATE) {
                    $h += $max;
                    $r = 0;
                    $max = 0;
                    ++$rows;
                } elseif($t===HIERO_CLUSTER) {
                    if($p->mdc!=='(') break;
                    else list($i,$rows,$r) = $nodes[$i+1]->getBlockHeight($padding);
                } elseif($t!==HIERO_JUXTAPOSITION) {
                    break;
                }
                if($r>$max) $max = $r;
            }
            $h += $max;
        }
//echo "getBlockHeight(index={$this->index}/{$i},height={$h})<br />\n";
        return [$i,$rows,$h];
    }
}


