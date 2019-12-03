<?php

return [
    "router" => [
        "routes" => [
            "/" => [
                "handler" => "App\\Controller\\Products"
            ],
            "products" => [
                "handler" => "App\\Controller\\Products"
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
        ]
    ]
];
