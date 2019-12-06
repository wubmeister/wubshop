<?php

use App\View\View;
use PHPUnit\Framework\TestCase;

final class App_View_ViewTest extends TestCase
{
    public function testCanRenderFile()
    {
        $file = __DIR__ . "/layout_template.phtml";
        $view = new View($file);
        $view->assign("layout_var", "foo");
        $view->assign("content", "The content");
        $content = $view->render();

        $this->assertEquals("Layout variable is foo\nContent is:\nThe content\n", $content);
    }

    public function testCanRenderNested()
    {
        $layoutFile = __DIR__ . "/layout_template.phtml";
        $viewFile = __DIR__ . "/view_template.phtml";

        $layout = new View($layoutFile);
        $view = new View($viewFile);

        $layout->assign("layout_var", "foo");
        $view->assign("view_var", "bar");
        $layout->assign("content", $view);

        $content = $layout->render();

        $this->assertEquals("Layout variable is foo\nContent is:\nView variable is bar\nParent layout variable is foo\nLorem ipsum\n\n", $content);
    }
}
