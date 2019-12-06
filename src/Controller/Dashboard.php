<?php

namespace App\Controller;

use Lib\Db\Schema;
use Lib\Tree;
use Lib\Template;
use Lib\View\View;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class Dashboard
{
    protected $navigation;

    public function __construct(Schema $schema)
    {
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $layout = new View(Template::find("layout"));
        $layout->assign("content", "");
        $layout->assign("navigation", $this->navigation);

        return new HtmlResponse($layout->render());
    }

    public function setNavigation(Tree $navigation)
    {
        $this->navigation = $navigation;
    }
}
