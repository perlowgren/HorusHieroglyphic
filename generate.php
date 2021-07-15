#!/usr/bin/php
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

/** Horus Hiero
 * 
 * This script generates PNG-images from hieroglyphic fonts, by extracting
 * the Unicode glyphs in the range 0x13000 to 0x1342E. The result is 1071
 * image files written to a given directory. A script containing the metrics
 * for the image glyphs is also generated and named "metrics-[font name].php".
 * 
 * @file generate.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-07
 * @date Created: 2013-01-02
 */

function utf8_ord($ch) {
    $len = strlen($ch);
    if($len<=0) return false;
    $h = ord($ch[0]);
    if($h<=0x7F) return $h;
    if($h<0xC2) return false;
    if($h<=0xDF && $len>1) return ($h&0x1F)<<6|(ord($ch[1])&0x3F);
    if($h<=0xEF && $len>2) return ($h&0x0F)<<12|(ord($ch[1])&0x3F)<<6|(ord($ch[2])&0x3F);
    if($h<=0xF4 && $len>3) return ($h&0x0F)<<18|(ord($ch[1])&0x3F)<<12|(ord($ch[2])&0x3F)<<6|(ord($ch[3])&0x3F);
    return false;
}

function utf8_chr($num) {
    if($num<0x80) return chr($num);
    if($num<0x800) return chr(($num>>6)+0xC0).chr(($num&0x3F)+0x80);
    if($num<0x10000) return chr(($num>>12)+0xE0).chr((($num>>6)&0x3F)+0x80).chr(($num&0x3F)+0x80);
    if($num<0x200000) return chr(($num>>18)+0xF0).chr((($num>>12)&0x3F)+0x80).chr((($num>>6)&0x3F)+0x80).chr(($num&0x3F)+0x80);
    return false;
}


require 'unicode.php';

$fonts = array(
	'Aegyptus'			=> array(0x13000,'Aegyptus-Regular'),
	'Aegyptus-Bold'	=> array(0x13000,'Aegyptus-Bold'),
	'Gardiner'			=> array(0x13000,'Gardiner-Regular'),
	'NewGardiner'		=> array(0xe000,'NewGardiner-Medium'),
);

if(strtoupper(substr(PHP_OS,0,3))==='WIN') {
	$green = '';
	$cyan = '';
	$brown = '';
	$none = '';
} else {
	$green = "\033[0;32m";
	$cyan = "\033[0;36m";
	$brown = "\033[0;33m";
	$none = "\033[0m";
}

$dir = '';
$font = 'Gardiner';
$stroke = '';
$color = 'black';
$size = '100';
$limit = -1;
for($i=0,$n=count($argv); $i<$n; ++$i) {
	$a = $argv[$i];
	if(strncmp($a,'-dir=',5)===0) $dir = substr($a,5);
	elseif(strncmp($a,'-font=',6)===0) $font = substr($a,6);
	elseif(strncmp($a,'-stroke=',8)===0) $stroke = substr($a,8);
	elseif(strncmp($a,'-color=',7)===0) $color = substr($a,7);
	elseif(strncmp($a,'-size=',6)===0) $size = substr($a,6);
	elseif(strncmp($a,'-limit=',7)===0) $limit = intval(substr($a,7));
	elseif($a=='-help' || $a=='--help' || $a=='-h') die('Generae Hieroglyphs
usage: generate_glyphs [options...]

Options:
  -dir=DIR                   output directory: [font] (default)
  -font=FONT                 font: Gardiner (default)
  -stroke=WIDTH              width (in points) of stroke: none (default)
  -color=COLOR               fill color: black (default)
  -size=SIZE                 size (in points) of font: 100 (default)
  -limit=LIMIT               number of glyphs to iterate (-1 for all): -1 (default)
');
}
if(!isset($fonts[$font])) die("Unknown font.\n");

if(!$dir) $dir = $font;
if(!is_dir($dir)) mkdir($dir);

if($limit!==0) {
	$unicode = $fonts[$font][0];
	$font = $fonts[$font][1];
	if($stroke) $stroke = '-stroke '.$color.' -strokewidth '.$stroke;
	else $stroke = '-stroke none';
	$pct = -1;
	$i = 0;
	$n = count($glyph_to_unicode);
	echo 'Using font: '.$font;
	foreach($glyph_to_unicode as $g=>$u) {
		$u = $unicode+$i;
		$c = utf8_chr($u);
		++$i;
		$x = "convert -background none -fill {$color} {$stroke} -font {$font} -pointsize {$size} label:'{$c}' -trim {$dir}/{$g}.png";
		if($pct!=($p=intval($i*100/$n))) {
			$pct = $p;
			echo "\n{$green}[%3d%%]{$cyan}",$pct);
		}
		echo " {$g}.png";
		exec($x);
		if($limit && $i==$limit) break;
	}
	echo "\n{$brown}Generated {$i} glyphs in '{$dir}'.{$none}\n";
}

$glyphs = array();
$n = 0;
if($dh=opendir($dir)) {
	while(($f=readdir($dh))!==false) {
		if($f[0]=='.') continue;
		$g = substr($f,0,strpos($f,'.png'));
		$s = getimagesize($dir.'/'.$f);
		$glyphs[] = sprintf("\t%-10s=> array('w'=>%3d,'h'=>%3d),","'{$g}'",$s[0],$s[1]);
		++$n;
	}
}

sort($glyphs);
$date = date('Y-m-d H:i:s');
$script = basename(__FILE__);
file_put_contents("metrics-{$font}.php","<?php
/* HorusHiero[{$date}] This script is generated for the font '{$font}' by:
 * {$script} */

use Horus\HorusHiero;

HorusHiero::setMetrics('{$font}',array(
".implode("\n",$glyphs)."
));

?>");
echo "Updated metrics for {$n} glyphs, saved to 'metrics-{$font}.php'.\n";


