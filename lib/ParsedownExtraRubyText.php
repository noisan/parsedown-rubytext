<?php
namespace Noi;

use ParsedownExtra;
use Noi\Parsedown\RubyTextTrait;

/**
 * ParsedownExtra ルビ用拡張記法Extension実装クラス
 *
 * @see \Noi\Parsedown\RubyTextTrait
 *
 * @copyright Copyright (c) 2015 Akihiro Yamanoi
 * @license MIT
 *
 * For the full license information, view the LICENSE file that was distributed
 * with this source code.
 */
class ParsedownExtraRubyText extends ParsedownExtra
{
    use RubyTextTrait;

    public function __construct()
    {
        parent::__construct();
        $this->registerRubyTextExtension();
    }
}
