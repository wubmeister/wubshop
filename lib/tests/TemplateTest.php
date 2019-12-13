<?php

use Lib\Template;
use PHPUnit\Framework\TestCase;

final class Lib_TemplateTest extends TestCase
{
    public function testResolvesPath()
    {
        $path = Template::find("layout");
        $this->assertEquals(dirname(__DIR__, 2) . "/templates/layout.phtml", $path);

        $path = Template::find("products/index");
        $this->assertEquals(dirname(__DIR__, 2) . "/templates/products/index.phtml", $path);
    }
}
