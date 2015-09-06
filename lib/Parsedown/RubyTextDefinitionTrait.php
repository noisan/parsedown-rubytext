<?php
namespace Noi\Parsedown;

/**
 * Parsedown ルビ定義用拡張記法Extension
 *
 * Parsedownを継承したクラスにルビ定義用の拡張記法を提供します。
 *
 * ルビを定義すると、MarkdownExtraの "Abbreviations" のように
 * 文書内の定義済み単語に自動でルビを振ることができます。
 * (inline codeとcode blockの文字列は対象外)
 *
 * traitなので親クラスは自由に選択できます。
 * このExtensionは\Noi\Parsedown\RubyTextTraitに依存していますので
 * 使用する際はRubyTextTraitも組み込んでください。
 *
 * Markdown:
 *
 *   以下の形式で漢字のルビを定義できます。
 *   空ルビ[形式]^()もルビ振り対象になります。
 *
 *   **[形式]: けいしき
 *   **[漢字]: かん じ
 *
 *   inline形式で明示的に書いたルビが[優先]^(ユウセン)です。
 *
 *    * リスト内の漢字にもルビを振ります。
 *    * 属性値付きで定義することもできます。
 *    * 空ルビの[属性値]^(){.inline}は、inline側が[優先]^(){.inline}です。
 *
 *   **[優先]:   ゆうせん
 *   **[属性値]: ぞくせいち {#id .class lang=ja}
 *
 *   `inline code内の漢字は対象外`です。もちろん
 *
 *   ```
 *   <div>コードブロック内の漢字も対象外です</div>
 *   ```
 *
 * Markdownの例と変換結果のHTMLは以下のファイルも確認してください。
 * @see tests/data/definition.md
 * @see tests/data/definition.html
 *
 * @copyright Copyright (c) 2015 Akihiro Yamanoi
 * @license MIT
 *
 * For the full license information, view the LICENSE file that was distributed
 * with this source code.
 */
trait RubyTextDefinitionTrait
{
    private $ruby_text_definition_ExtensionEnabled    = true;
    private $ruby_text_definition_ExtensionRegistered = false;

    public function registerRubyTextDefinitionExtension()
    {
        if ($this->ruby_text_definition_ExtensionRegistered) {
            return;
        }

        $this->ruby_text_definition_ExtensionRegistered = true;

        $this->BlockTypes['*'][] = 'RubyTextDefinition';
        $this->ruby_text_definition_ElementBuilderName = $this->setRubyTextElementBuilderName('buildRubyTextElementWrapper');
    }

    protected function blockRubyTextDefinition($Line)
    {
        if (!$this->isRubyTextDefinitionEnabled()) {
            return;
        }

        if (!$this->matchRubyTextDefinitionMarkdown($Line['text'], $kanji, $furigana, $attributes)) {
            return;
        }

        $this->defineRubyText($kanji, $furigana, $attributes);

        return array(
            'hidden' => true,
        );
    }

    protected function matchRubyTextDefinitionMarkdown($line, /* out */ &$kanji, /* out */ &$furigana, /* out */ &$attributes)
    {
        /* 以下の記法をルビ定義と解釈する:
         *   1. **[空文字]:
         *   2. **[単語]:   たんご
         *   3. **[日本語]: に ほん ご
         *   4. **[属性値]: ぞくせいち  {#id .class lang=ja}
         *
         * Markdown(Parsedown?)の仕様に従い、定義の行頭はインデント可能。
         * 1は空ルビ、2はグループルビ、3はモノルビ、4は属性値付きの定義書式。
         */

        /* まず行頭のprefixをチェック:
         * prefixの文字列 "**" は他の拡張記法と競合するかもしれない。
         * 書式を変更しやすくするためにメソッドを分けておく。
         */
        if (!$this->matchRubyTextDefinitionPrefix($line, $rest)) {
            return false;
        }

        // 残りの "[親文字]: ふりがな {attrs}" の部分をチェック(属性値は省略可):
        if (!$this->matchRubyTextDefinitionBase($rest, $kanji, $furigana, $attributes)) {
            return false;
        }

        return true;
    }

    protected function matchRubyTextDefinitionPrefix($text, /* out */ &$rest)
    {
        if (strncmp($text, '**', 2) === 0) {
            $rest = substr($text, 2);
            return true;
        }

        return false;
    }

    protected function matchRubyTextDefinitionBase($text, /* out */ &$kanji, /* out */ &$furigana, /* out */ &$attributes)
    {
        // "[親文字]" の部分をチェック。再帰的パターン(?R)を使って対応括弧までを見る
        if (!preg_match('/\[((?:(?>[^][]+)|(?R))*)\]/Au', $text, $m)) {
            return false;
        }

        // 親文字が空の場合はNGとする
        $tmp_kanji = trim($m[1]);

        if ($tmp_kanji === '') {
            return false;
        }

        $rest = substr($text, strlen($m[0]));

        /* ":" の部分をチェック。ここまでマッチすれば正しい形式とする。
         * ParsedownExtra::blockAbbreviation() の実装に合わせて
         * ":" の前に空白は置けない。後の空白はいくつでもOK
         */
        if (($rest === '') or ($rest[0] !== ':')) {
            return false;
        }

        // 有効な書式なので[親文字]を確定
        $kanji = $tmp_kanji;

        // ":" の後の空白もParsedownExtraの実装を参考に"\x20"のみを削る(\sではない)
        $rest = trim(substr($rest, 1), ' ');

        // {#id .class attr=val} の部分(属性値)をチェック。属性値は省略可能
        if (preg_match('/[ ]*{([^}]*)}$/u', $rest, $m, PREG_OFFSET_CAPTURE)) {
            $attributes = $this->parseRubyTextAttributeData($m[1][0]);
            $rest = substr($rest, 0, $m[0][1]);
        }

        $furigana = $rest;
        return true;
    }

    protected function defineRubyText($kanji, $furigana, $attributes = null)
    {
        $this->DefinitionData['RubyTextDefinition'][$kanji[0]][$kanji] = array($furigana, $attributes, strlen($kanji));

        // memo: ソートはもっと効率良い場所に移す
        uasort($this->DefinitionData['RubyTextDefinition'][$kanji[0]], function ($a, $b) {
            return $b[2] - $a[2];  // order by strlen() DESC
        });
    }

    protected function lookupRubyTextDefinition($kanji)
    {
        if (isset($this->DefinitionData['RubyTextDefinition'][$kanji[0]][$kanji])) {
            return $this->DefinitionData['RubyTextDefinition'][$kanji[0]][$kanji];
        }

        return null;
    }

    protected function buildRubyTextElementWrapper($kanji, $furigana, $attributes)
    {
        if (($furigana === '') and ($defined = $this->lookupRubyTextDefinition($kanji))) {
            // 空ルビのふりがなと属性値を補完する
            if ($this->getRubyTextDefinitionNestCount() < 2) {
                $furigana = $defined[0];  // ルビのルビまでは補完する
            }
            if ($attributes === null) {
                $attributes = $defined[1];
            }
        }

        $Element = $this->{$this->ruby_text_definition_ElementBuilderName}($kanji, $furigana, $attributes);
        $Element['text']['handler'] = $Element['handler'];
        $Element['handler'] = 'ruby_element_wrapper';
        return $Element;
    }

    protected function ruby_element_wrapper($Element)
    {
        /* ルビのネストレベルを計測する:
         *   ここはLv0 [ここはLv1]^(ここもLv1[ここはLv2]^(ここもLv2)こっちはLv1) ここはLv0
         *
         * ネストLvは以下の2箇所で重要になる:
         *   1. 自動ルビ付け(unmarkedText()内部)
         *   2. 空ルビのふりがな補完(buildRubyTextElementWrapper()内部)
         *
         * memo: どちらも自動参照の無限ループを防止する必要がある。
         * 1ではルビの中を自動ルビ付けしないことで無限ループを防止する。ネストLv0のみで有効。
         * 2では補完をネストLv2以上で無効にすることで無限ループしないようにする。
         * ネストLv0とLv1で有効(ルビ2行分を目安にした。Lv2以降は空ルビにふりがなを補完しない)。
         */
        $this->ruby_text_definition_NestCount++;

        $markup = $this->{$Element['handler']}($Element);

        $this->ruby_text_definition_NestCount--;

        return $markup;
    }

    protected function getRubyTextDefinitionNestCount()
    {
        return $this->ruby_text_definition_NestCount;
    }

    // override
    protected function unmarkedText($text)
    {
        /* 以下を実行した時点で親クラスによってmarkupされている可能性がある。
         * 例えばParsedownExtraを継承していると "Abbreviations" のタグが付く。
         */
        $text = parent::unmarkedText($text);

        if (!$this->isRubyTextDefinitionEnabled()) {
            return $text;
        }

        if (!isset($this->DefinitionData['RubyTextDefinition'])) {
            // ルビを定義していないのでスキップ
            return $text;
        }

        if ($this->getRubyTextDefinitionNestCount()) {
            // 無限ループ防止のため、ルビの中は自動ルビ付けしない
            return $text;
        }

        return $this->unmarkedRubyText($text);
    }

    /*
     * 文字列に含まれる各単語にルビが定義済みであればルビを振る。
     *
     * 前提条件として、引数の文字列はMarkdown記号を含まない平文で
     * code要素でもないとする。ただし、HTMLタグは含んでいるかもしれない。
     *
     * 戻り値はルビ用タグを埋め込んだ結果の文字列。
     */
    protected function unmarkedRubyText($text)
    {
        // ルビはタグの外側を対象とする。属性値を対象にしてはダメ
        return preg_replace_callback('/((?:\G|>))([^<]+)/u', array($this, 'replaceUnmarkedRubyTextCallback'), $text);
    }

    protected function replaceUnmarkedRubyTextCallback($m)
    {
        return $m[1] . $this->replaceUnmarkedRubyText($m[2]);
    }

    protected function replaceUnmarkedRubyText($text)
    {
        $replaced = '';

        /* $textの先頭から1byteずつ調べながらルビ定義済みの単語にルビを振っていく。
         * 同じ文字で始まる親文字が複数定義されていたら最長一致する単語を優先する。
         *
         * (もっと良いアルゴリズムに変えたい)
         */
        for ($i = 0, $len = strlen($text); $i < $len; /* $i++ */ ) {
            $marker = $text[$i];

            // 検索しやすいように親文字の1byte目ごとに索引を作っている
            if (isset($this->DefinitionData['RubyTextDefinition'][$marker])) {

                // 前提条件: 各索引の定義リストは$kanjiの文字数降順でソート済み(order by strlen($kanji) DESC)
                foreach ($this->DefinitionData['RubyTextDefinition'][$marker] as $kanji => $defined) {
                    if (substr_compare($text, $kanji, $i, $defined[2]) === 0) {
                        // ルビを振る
                        $replaced .= $this->element(
                                $this->{$this->getRubyTextElementBuilderName()}($kanji, $defined[0], $defined[1]));

                        $i += $defined[2];
                        continue 2;
                    }
                }
            }

            $replaced .= $marker;
            $i++;
        }

        return $replaced;
    }

    public function setRubyTextDefinitionEnabled($bool)
    {
        $this->ruby_text_definition_ExtensionEnabled = $bool;
        return $this;
    }

    public function isRubyTextDefinitionEnabled()
    {
        return $this->ruby_text_definition_ExtensionEnabled;
    }

    abstract protected function setRubyTextElementBuilderName($name);
    abstract protected function getRubyTextElementBuilderName();
    abstract protected function parseRubyTextAttributeData($attributeString);

    protected $ruby_text_definition_ElementBuilderName;
    protected $ruby_text_definition_NestCount = 0;
}
