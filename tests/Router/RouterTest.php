<?php

use App\Router\Router;
use PHPUnit\Framework\TestCase;

final class App_Router_RouterTest extends TestCase
{
    protected $config = [
        "/" => [
            "handler" => "App\\Controller\\Products"
        ],
        "products" => [
            "handler" => "App\\Controller\\Products",
            "children" => [
                "variants" => [
                    "handler" => "App\\Controller\\Products\\Variants"
                ]
            ]
        ],
        "product-types" => [
            "handler" => "App\\Controller\\ProductTypes",
            "children" => [
                "attributes" => [
                    "handler" => "App\\Controller\\ProductTypes\\Attributes"
                ]
            ]
        ],
        "channels" => [
            "handler" => "App\\Controller\\Channels"
        ],
        "languages" => [
            "handler" => "App\\Controller\\Languages"
        ],
    ];

    public function testResolvesSimplePaths()
    {
        $router = new Router($this->config);

        $result = $router->resolve("GET", "/products");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products", $result["handler"]);
        $this->assertEmpty($result["params"]["ids"]);

        $result = $router->resolve("GET", "/products/12");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products", $result["handler"]);
        $this->assertEquals("12", $result["params"]["id"]);
        $this->assertEquals("12", $result["params"]["ids"]["products"]);

        $result = $router->resolve("GET", "/products/add");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products", $result["handler"]);
        $this->assertEquals("add", $result["params"]["action"]);
        $this->assertEmpty($result["params"]["ids"]);

        $result = $router->resolve("GET", "/products/edit/43");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products", $result["handler"]);
        $this->assertEquals("edit", $result["params"]["action"]);
        $this->assertEquals("43", $result["params"]["id"]);
        $this->assertEquals("43", $result["params"]["ids"]["products"]);
    }

    public function testResolvesNestedPaths()
    {
        $router = new Router($this->config);

        $result = $router->resolve("GET", "/products/12/variants");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products\\Variants", $result["handler"]);
        $this->assertEquals(null, $result["params"]["id"]);
        $this->assertEquals("12", $result["params"]["ids"]["products"]);

        $result = $router->resolve("GET", "/products/12/variants/68");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products\\Variants", $result["handler"]);
        $this->assertEquals("68", $result["params"]["id"]);
        $this->assertEquals("12", $result["params"]["ids"]["products"]);
        $this->assertEquals("68", $result["params"]["ids"]["variants"]);

        $result = $router->resolve("GET", "/products/12/variants/add");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products\\Variants", $result["handler"]);
        $this->assertEquals("add", $result["params"]["action"]);
        $this->assertEquals(null, $result["params"]["id"]);
        $this->assertEquals("12", $result["params"]["ids"]["products"]);

        $result = $router->resolve("GET", "/products/12/variants/edit/43");
        $this->assertIsArray($result);
        $this->assertEquals("App\\Controller\\Products\\Variants", $result["handler"]);
        $this->assertEquals("edit", $result["params"]["action"]);
        $this->assertEquals("43", $result["params"]["id"]);
        $this->assertEquals("12", $result["params"]["ids"]["products"]);
        $this->assertEquals("43", $result["params"]["ids"]["variants"]);
    }
}
