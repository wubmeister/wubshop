<?php

use Lib\View\Helper;
use PHPUnit\Framework\TestCase;

final class Lib_View_HelperTest extends TestCase
{
    public function testLoadHelper()
    {
        $helper = new Helper();
        $generalHelper = $helper->general;

        $this->assertIsObject($generalHelper);
        $this->assertInstanceOf("Lib\\View\\Helper\\General", $generalHelper);
    }
}
