<?php

use Lib\Form\Field\File as FileField;
use PHPUnit\Framework\TestCase;

final class Lib_Field_FileTest extends TestCase
{
    public function testGetFileValue()
    {
        $_FILES = [ "fieldname" => [
            "name" => "my_uploaded_file.txt",
            "type" => "text/plain",
            "size" => 43008,
            "tmp_name" => "/tmp/upload_123456",
            "error" => UPLOAD_ERR_OK
        ] ];

        $field = new FileField("fieldname");
        $value = $field->getValue();
        $this->assertIsArray($value);
        $this->assertCount(5, $value);
        foreach ($_FILES["fieldname"] as $key => $val) {
            $this->assertEquals($val, $value[$key]);
        }
    }
}
