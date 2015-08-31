<?php
namespace Noi\Tests;

use Noi\ParsedownRubyText;
use ParsedownTest;

class ParsedownRubyTextTest extends ParsedownTest
{
    use RubyTextTestTrait;

    protected function initParsedown()
    {
        $Parsedown = new ParsedownRubyText();

        return $Parsedown;
    }
}
