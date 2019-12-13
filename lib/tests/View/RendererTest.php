<?php

use Lib\View\Renderer;
use PHPUnit\Framework\TestCase;

final class Lib_View_RendererTest extends TestCase
{
    public function testCanRenderFile()
    {
        $file = __DIR__ . "/renderer_template.phtml";
        $renderer = new Renderer();
        $renderer->assign("who", "world");
        $content = $renderer->render($file);

        $this->assertEquals("Hello world...\n", $content);
    }
}
