<?php
namespace Noi\Tests;

use Noi\ParsedownExtraRubyText;
use ParsedownExtraTest;

class ParsedownExtraRubyTextTest extends ParsedownExtraTest
{
    use RubyTextTestTrait;

    protected function initParsedown()
    {
        $Parsedown = new ParsedownExtraRubyText();

        return $Parsedown;
    }
}
