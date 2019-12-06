<?php

use App\Template;
use PHPUnit\Framework\TestCase;

final class App_TemplateTest extends TestCase
{
    public function testResolvesPath()
    {
        $path = Template::find("layout");
        $this->assertEquals(dirname(__DIR__) . "/templates/layout.phtml", $path);

        $path = Template::find("products/index");
        $this->assertEquals(dirname(__DIR__) . "/templates/products/index.phtml", $path);
    }
}
