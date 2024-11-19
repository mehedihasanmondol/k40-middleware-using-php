<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/9/2021
 * Time: 3:29 PM
 */

class ModelFieldDesigner
{
    public $name = "";
    public $type = "";
    public $default = "";
    public $length = "";
    public $index = "";

    public function __construct($name,$length,$type,$index="",$default="")
    {
        $this->name = $name;
        $this->length = $length;
        $this->index = $index;
        $this->type = $type;
        $this->default = $default;
    }
    public function field_schema(){
        return get_object_vars($this);
    }
}