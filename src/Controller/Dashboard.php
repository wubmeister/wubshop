<?php

namespace App\Controller;

use App\Db\Schema;
use App\Tree;
use App\Template;
use App\View\View;
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
