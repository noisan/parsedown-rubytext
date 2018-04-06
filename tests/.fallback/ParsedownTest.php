<?php
use PHPUnit\Framework\TestCase;

abstract class ParsedownTest extends TestCase
{
    private $dirs;
    private $Parsedown;

    final public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->dirs      = $this->initDirs();
        $this->Parsedown = $this->initParsedown();

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return array
     */
    protected function initDirs()
    {
        $dirs[] = dirname(__FILE__) . '/data/';
        return $dirs;
    }

    /**
     * @return Parsedown
     */
    protected function initParsedown()
    {
        $Parsedown = new TestParsedown();
        return $Parsedown;
    }
}
