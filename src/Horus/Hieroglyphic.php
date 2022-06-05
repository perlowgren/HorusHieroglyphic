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

use Spirangle\Horus\Formatters\HtmlFormatter;

define('ROOT_DIR',realpath(__DIR__.'/../../'));

define('HIERO_DEFAULT_FONT','NewGardiner');
define('HIERO_DEFAULT_FORMAT',0);
define('HIERO_DEFAULT_OUTPUT','html');

define('HIERO_MANUEL_DE_CODAGE',0);

define('HIERO_NONE',0x0000);
define('HIERO_GLYPH',0x0001);
define('HIERO_SEPARATOR',0x0002);
define('HIERO_BLANK',0x0004);
define('HIERO_SUBORDINATE',0x0008);
define('HIERO_JUXTAPOSITION',0x0010);
define('HIERO_CLUSTER',0x0020);
define('HIERO_CARTOUCHE',0x0040);
define('HIERO_END',0x0080);
define('HIERO_TEXT',0x0100);

define('HIERO_BLOCK',0x0038);
define('HIERO_SHOW',0x0145);
define('HIERO_HIDE',0x00BA);

abstract class Hieroglyphic {
    const SEPARATOR_PATTERN = '/(!!|\.\.| {2}|__|[\t\n _\-:*.!()|]|<h?[0123]?|[0123]?h?>)/';

    protected static array $phonemes = [];
    protected static array $metrics = [];
    protected static array $formatters = [
        'html' => HtmlFormatter::class
    ];

    protected string $font;
    protected string $text;
    protected array $codes;
    protected array $nodes;

    public static function parse(string $text,array $params): string {
        $font = $params['font'] ?? HIERO_DEFAULT_FONT;
        $output = $params['output'] ?? HIERO_DEFAULT_OUTPUT;
        $formatter = self::$formatters[isset(self::$formatters[$output])? $output : HIERO_DEFAULT_OUTPUT];
        $hiero = new $formatter($text,$font,$params);
        return $hiero->process();
    }

    /**
     * @throws HieroglyphicException
     */
    function __construct(string $text,string $font,array $params) {
        if(empty(self::$phonemes)) {
            $phonemesScript = ROOT_DIR."/src/phonemes.php";
            if(!file_exists($phonemesScript)) {
                throw new HieroglyphicException("Hieroglyphic: Phonemes missing.");
            }
            self::$phonemes = require $phonemesScript;
        }
        $metricsScript = ROOT_DIR."/src/metrics/$font.php";
        if(!isset(self::$metrics[$font])) {
            if(!file_exists($metricsScript)) {
                throw new HieroglyphicException("Hieroglyphic: Metrics for '{$font}' missing.");
            }
            self::$metrics[$font] = require $metricsScript;
        }
        $this->font = $font;
        $this->text = $text;
        $flags = PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE;
        $this->codes = preg_split(self::SEPARATOR_PATTERN,"{$text}-",-1,$flags);
        $i = 0;
        $this->nodes = [];
        foreach($this->codes as $c) {
            $this->nodes[] = new HieroglyphicNode($this,$i++,$c);
        }
        for($i = 0,$n = count($this->nodes); $i+1<$n; ++$i) {
            $this->nodes[$i]->next = $this->nodes[$i+1]->type;
        }
        for($i = 0; $i<$n;) {
            $i = $this->nodes[$i]->rescale();
        }
    }

    public static function getPhonemes(): array {
        return self::$phonemes;
    }

    public function getMetrics(): array {
        return self::$metrics[$this->font];
    }

    public function getNodes(): array {
        return $this->nodes;
    }

    protected function process(): string {
        return $this->text;
    }
}


