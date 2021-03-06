<?php

declare(strict_types=1);

namespace Spirangle\Horus\Tests;

use PHPUnit\Framework\TestCase;
use Spirangle\Horus\Hieroglyphic;

class HieroglyphicTest extends TestCase {

    /** @noinspection HtmlUnknownTarget */
    public function testHierolgyphicHtmlForKhepr(): void
    {
        $text = "x:p-xpr:r-i-A40";
        $html = Hieroglyphic::parse($text,[
            'font' => 'NewGardiner',
            'output' => 'html',
            'font-url' => 'glyphs',
            'height' => 30,
            'lines' => false,
            'comment' => 'Generated by Horus Hieroglyphic',
        ]);
        $expectedHtml = <<<HTML
<!-- Generated by Horus Hieroglyphic -->
<ul class="hiero hiero-ltr" style="height:30px;"><!--
  --><li style="margin-top:6px;"><img src="glyphs/Q3.png" alt="Q3" width="13" height="17" /></li><!--
  --><li><ul class="hiero-subordinate"><!--
    --><li><img src="glyphs/L1.png" alt="L1" width="14" height="22" /></li><!--
    --><li><img src="glyphs/D21.png" alt="D21" width="22" height="7" /></li><!--
   --></ul><!--SUBORDINATE
   --><div style="clear:both;"></div></li><!--
   --><li><img src="glyphs/M17.png" alt="M17" width="7" height="29" /></li><!--
   --><li><img src="glyphs/A40.png" alt="A40" width="20" height="29" /></li><!--
--></ul>

HTML;

        $this->assertNotNull($html);
        $this->assertEquals($html,$expectedHtml);
    }
}
