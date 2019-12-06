<?php

use App\View\Helper;
use PHPUnit\Framework\TestCase;

final class App_View_HelperTest extends TestCase
{
    public function testLoadHelper()
    {
        $helper = new Helper();
        $generalHelper = $helper->general;

        $this->assertIsObject($generalHelper);
        $this->assertInstanceOf("App\\View\\Helper\\General", $generalHelper);
    }
}
