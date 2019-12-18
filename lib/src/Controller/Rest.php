<?php

namespace Lib\Controller;

use Lib\HttpException;
use Lib\Template;
use Lib\View\View;
use Psr\Http\Message\ServerRequestInterface;

class Rest
{
    protected $layout;
    protected $request;
    protected $id;

    public function __invoke(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $this->id = $request->getAttribute("id");
        $forcedAction = $request->getAttribute("action");
        $action = "index";

        if ($forcedAction) {
            $action = $forcedAction;
        } else {
            switch ($method) {
                case "POST":
                case "PUT":
                    $action = $this->id ? "update" : "create";
                    break;

                case "DELETE":
                    if ($this->id) $action = "delete";
                    else throw new HttpException(405);
                    break;

                case "GET":
                    $action = $this->id ? "show" : "index";
                    break;

                default:
                    throw new HttpException(405);
            }
        }

        if (!method_exists($this, $action)) {
            throw new HttpException(404);
        }
        if (!$this->isAllowed($action)) {
            throw new HttpException(401);
        }

        $this->request = $request;
        $this->layout = new View(Template::find("layout"));

        $this->beforeDispatch($action);

        return $this->$action();
    }

    protected function beforeDispatch(string $action)
    {
    }

    protected function isAllowed($action)
    {
        return true;
    }
}
