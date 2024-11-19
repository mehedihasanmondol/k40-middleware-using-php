<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/13/2021
 * Time: 11:00 AM
 */

namespace App\User\Model;

trait UserTrait{
    public $id = "";
    public $uid = "";
    public $name = "";
    public $roll = "";
    public $password = "";
}

class User{
    use UserTrait;
}
class UserSchema implements \ModelInterface
{
    use UserTrait;
    public function get_schema(){
        /// to be continue when available time

        $this->id = new \ModelFieldDesigner("id",0,(new \ModelFieldType())->integer,"primary","0");
        $this->uid = new \ModelFieldDesigner("uid",0,(new \ModelFieldType())->integer,"uid","0");
        $this->name = new \ModelFieldDesigner("name","",(new \ModelFieldType())->string,"",null);
        $this->roll = new \ModelFieldDesigner("roll","100",(new \ModelFieldType())->varchar,"",null);
        $this->password = new \ModelFieldDesigner("password","100",(new \ModelFieldType())->varchar,"",null);
        return get_object_vars($this);
    }
    public function table_name()
    {
        // TODO: Implement table_name() method.
        return "users";
    }
}

class UserView extends User {
    public function __construct()
    {
        $this->id = "users.id";
        $this->name = "users.name";
        $this->uid = "users.uid";
        $this->roll = "users.roll";
        $this->password = "users.password";

    }
}

