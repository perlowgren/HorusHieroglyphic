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

define('HIERO_DEFAULT_FONT',     'NewGardiner');  //!< 
define('HIERO_DEFAULT_FORMAT',   0);              //!< 
define('HIERO_DEFAULT_OUTPUT',   'html');         //!< 

define('HIERO_MANUEL_DE_CODAGE', 0);              //!< 

define('HIERO_NONE',             0x0000);         //!< 
define('HIERO_GLYPH',            0x0001);         //!< 
define('HIERO_SEPARATOR',        0x0002);         //!< 
define('HIERO_BLANK',            0x0004);         //!< 
define('HIERO_SUBORDINATE',      0x0008);         //!< 
define('HIERO_JUXTAPOSITION',    0x0010);         //!< 
define('HIERO_CLUSTER',          0x0020);         //!< 
define('HIERO_CARTOUCHE',        0x0040);         //!< 
define('HIERO_END',              0x0080);         //!< 
define('HIERO_TEXT',             0x0100);         //!< 

define('HIERO_BLOCK',            0x0038);         //!< 
define('HIERO_SHOW',             0x0145);         //!< 
define('HIERO_HIDE',             0x00BA);         //!< 

class HorusHiero {
	/**  */
	const SEPARATOR_PATTERN = '/(\!\!|\.\.|  |__|[\t\n _\-\:\*\.\!\(\)\|]|\<h?[0123]?|[0123]?h?\>)/';

	protected static $phonemes = false;  //!< 
	protected static $metrics = array(); //!< 
	protected static $handlers = array(  //!< 
		'html'=>'HorusHTML'
	);

	protected $font;       //!< 
	protected $text;       //!< 
	protected $codes;      //!< 
	protected $nodes;      //!< 

	public static function parse($text,$params) {
		$font     = isset($params['font'])?   $params['font']   : HIERO_DEFAULT_FONT;
		$format   = isset($params['format'])? $params['format'] : HIERO_DEFAULT_FORMAT;
		$output   = isset($params['output'])? $params['output'] : HIERO_DEFAULT_OUTPUT;
		$handler  = self::$handlers[isset(self::$handlers[$output])? $output : HIERO_DEFAULT_OUTPUT];
		$class    = "Horus\\{$handler}";
		$horus    = new $class($text,$font,$format,$params);
		return $horus->process();
	}

	/** 
	 * @param $text Text to format
	 * @param $font Name of font to use
	 * @param $format Format of text (only Manuel de Codage available at the moment)
	 * @param $params Extra handler specific params
	 */
	function __construct($text,$font,$format,$params) {
		$this->font = $font;
		$this->text = $text;
		$this->codes = preg_split(self::SEPARATOR_PATTERN,"{$text}-",-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$this->nodes = false;
		if(self::$phonemes===false)
			error_log("HorusHiero: Phonemes missing.");
		elseif(!isset(self::$metrics[$font]))
			error_log("HorusHiero: Metrics for '{$font}' missing.");
		else {
			$i = 0;
			$this->nodes = array();
			foreach($this->codes as $c)
				$this->nodes[] = new HorusNode($this,$i++,$c);
			for($i=0,$n=count($this->nodes); $i+1<$n; ++$i)
				$this->nodes[$i]->next = $this->nodes[$i+1]->type;
			for($i=0; $i<$n; )
				$i = $this->nodes[$i]->rescale();
		}
	}

	public static function setPhonemes($phonemes) { self::$phonemes = $phonemes; }
	public static function getPhonemes() { return self::$phonemes; }

	public static function setMetrics($font,$metrics) { self::$metrics[$font] = $metrics; }
	public function getMetrics() { return self::$metrics[$this->font]; }

	public function &getNodes() { return $this->nodes; }

	protected function process() { return $this->text; }
}


