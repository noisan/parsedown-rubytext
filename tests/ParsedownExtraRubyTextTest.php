<?php
namespace Noi\Tests;

use Noi\ParsedownExtraRubyText;
use ParsedownExtraTest;

class ParsedownExtraRubyTextTest extends ParsedownExtraTest
{
    protected function initDirs()
    {
        $dirs = parent::initDirs();

        $dirs []= dirname(__FILE__).'/data/';

        return $dirs;
    }

    protected function initParsedown()
    {
        $Parsedown = new ParsedownExtraRubyText();

        return $Parsedown;
    }

    /** @test */
    public function 指定した括弧を使ってrpタグを出力する_rp用の括弧を変更()
    {
        $markdown = '[紫電の槍]^(ライトニングスピア)';
        $expected = '<ruby>紫電の槍<rp>＜</rp><rt>ライトニングスピア</rt><rp>＞</rp></ruby>';

        $this->parsedown->setRubyTextBrackets('＜', '＞');

        $this->assertEquals($expected, $this->parsedown->line($markdown));
    }

    /** @test */
    public function 指定文字列でルビを分割する_モノルビ用の分割記号を変更()
    {
        $markdown = '[東京都]^(とう/きょう/と)';
        $expected = '<ruby>東<rp>（</rp><rt>とう</rt><rp>）</rp>京<rp>（</rp><rt>きょう</rt><rp>）</rp>都<rp>（</rp><rt>と</rt><rp>）</rp></ruby>';

        $this->parsedown->setRubyTextSeparator('/');

        $this->assertEquals($expected, $this->parsedown->line($markdown));
    }

    public function setUp()
    {
        $this->parsedown = $this->initParsedown();
    }

    protected $parsedown;
}
