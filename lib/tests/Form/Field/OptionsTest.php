<?php

use Lib\Form\Field\Options as OptionsField;
use PHPUnit\Framework\TestCase;

final class Lib_Field_OptionsTest extends TestCase
{
    public function testCanHaveOptions()
    {
        $field = new OptionsField("fieldname");
        $field->addOption("foo", "bar");

        $options = $field->getOptions();
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertEquals("bar", $options["foo"]);

        $field = new OptionsField("fieldname", [ "options" => [ "lorem" => "ipsum" ] ]);

        $options = $field->getOptions();
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertEquals("ipsum", $options["lorem"]);

        $field->addOption("foo", "bar");
        $options = $field->getOptions();
        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertEquals("ipsum", $options["lorem"]);
        $this->assertEquals("bar", $options["foo"]);

    }
}
