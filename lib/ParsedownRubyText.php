<?php
namespace Noi;

use Parsedown;
use Noi\Parsedown\RubyTextTrait;

/**
 * Parsedown ルビ用拡張記法Extension実装クラス
 *
 * Markdown:
 *   1. [親文字]^(ルビ)  -- 基本形式 [base]^(ruby)
 *   2. [親文字]^（ルビ）-- ^あり全角括弧形式
 *   3. [親文字]（ルビ） -- ^なし全角括弧形式
 *
 *   // HTML:
 *   <ruby>親文字<rp>（</rp><rt>ルビ<rt><rp>）</rp></ruby>
 *   <ruby>base<rp>（</rp><rt>ruby<rt><rp>）</rp></ruby>
 *
 * Usage:
 *   $p = new Noi\ParsedownRubyText();
 *   echo $p->text('Parsedownはとても[便利]^(べんり)');
 *   // Output:
 *   <p>Parsedownはとても<ruby>便利<rp>（</rp><rt>べんり</rt><rp>）</rp></ruby></p>
 *
 * @see \Noi\Parsedown\RubyTextTrait
 *
 * @copyright Copyright (c) 2015 Akihiro Yamanoi
 * @license MIT
 *
 * For the full license information, view the LICENSE file that was distributed
 * with this source code.
 */
class ParsedownRubyText extends Parsedown
{
    use RubyTextTrait;

    public function __construct()
    {
        $this->registerRubyTextExtension();
    }
}
