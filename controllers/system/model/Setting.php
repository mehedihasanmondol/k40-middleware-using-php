<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/7/2021
 * Time: 1:27 PM
 */

namespace System\Setting\Model;

trait SettingTrait{
    public $id = "";
    public $type = "";
    public $value = '';
}
class Setting{
    use SettingTrait;
}
class SettingSchema implements \ModelInterface
{
    use SettingTrait;

    public function get_schema(){
        /// to be continue when available time

        $this->id = new \ModelFieldDesigner("id",0,(new \ModelFieldType())->integer,"primary","0");
        $this->type = new \ModelFieldDesigner("type","200",(new \ModelFieldType())->varchar,"","");
        $this->value = new \ModelFieldDesigner("value","",(new \ModelFieldType())->string,"","");
        return get_object_vars($this);
    }
    public function table_name()
    {
        // TODO: Implement table_name() method.
        return "settings";
    }
}

class SettingView
{
    public $type = "settings.type";
    public $value = "settings.value";
}
class SettingPropertyView{
    public $title = true;
    public $time_zone = true;
    public $time_format = true;
    public $logo = true;
    public $favicon = true;
    public $email = true;
    public $company_name = true;
    public $ip = true;
    public $server = true;
}



