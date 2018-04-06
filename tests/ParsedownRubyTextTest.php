<?php
namespace Noi\Tests;

use Noi\ParsedownRubyText;
use ParsedownTest;

class ParsedownRubyTextTest extends ParsedownTest
{
    use RubyTextTestTrait;
    use RubyTextDefinitionTestTrait;

    protected function initParsedown()
    {
        $Parsedown = new ParsedownRubyText();

        return $Parsedown;
    }

    /**
     * @runInSeparateProcess
     * @requires function ParsedownTest::testLateStaticBinding
     */
    public function testLateStaticBinding()
    {
        return parent::testLateStaticBinding();
    }
}
