<?php

use App\Tree;
use PHPUnit\Framework\TestCase;

final class App_TreeTest extends TestCase
{
    public function testCanBeCreatedFromArray()
    {
        $tree = Tree::fromArray([ "foo" => 1, "bar" => 2 ]);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals(1, $tree->foo);
        $this->assertEquals(2, $tree->bar);
    }

    public function canGetChildrenNamed()
    {
        $tree = Tree::fromArray([
            "foo" => 1,
            "bar" => 2,
            "children" => [
                "lorem" => [ "foo" => 3, "bar" => 4 ],
                "ipsum" => [ "foo" => 5, "bar" => 6, "children" => [
                    "doler" => [ "foo" => 7, "bar" => 8 ]
                ] ],
            ]
        ]);

        $children = $tree->getChildrenNamed();

        $this->assertIsArray($children);
        $this->assertArrayHasKey("lorem", $children);
    }

    public function testCanCascadeProperties()
    {
        $tree = Tree::fromArray([
            "foo" => 1,
            "bar" => 2,
            "children" => [
                "lorem" => [ "foo" => 3, "bar" => 4 ],
                "ipsum" => [ "foo" => 5, "bar" => 6, "children" => [
                    "doler" => [ "foo" => 7, "bar" => 8 ]
                ] ],
            ]
        ]);

        $tree->cascadeProperty("ipsum/doler", "prop", "value");

        $children = $tree->getChildrenNamed();
        $this->assertEquals("value", $children["ipsum"]->prop);
        $children = $children["ipsum"]->getChildrenNamed();
        $this->assertEquals("value", $children["doler"]->prop);
    }

    public function testCanCascadeReplace()
    {
        $tree = Tree::fromArray([
            "foo" => 1,
            "bar" => 2,
            "children" => [
                "lorem" => [ "foo" => "Lorem #name#", "bar" => 4 ],
                "ipsum" => [ "foo" => "Ipsum #name#", "bar" => 6, "children" => [
                    "doler" => [ "foo" => "Doler #name#", "bar" => 8 ]
                ] ],
            ]
        ]);

        $tree->cascadePropertyReplace("ipsum/doler", "foo", "#name#", "Test");

        $children = $tree->getChildrenNamed();
        $this->assertEquals("Ipsum Test", $children["ipsum"]->foo);
        $children = $children["ipsum"]->getChildrenNamed();
        $this->assertEquals("Doler Test", $children["doler"]->foo);
    }
}
