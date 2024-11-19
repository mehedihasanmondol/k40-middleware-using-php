<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/13/2021
 * Time: 11:00 AM
 */

namespace App\AttendanceLog\Model;

trait AttendanceLogTrait{
    public $id = "";
    public $uid = "";
    public $state = "";
    public $time = "";
    public $sync = "";
}

class AttendanceLog{
    use AttendanceLogTrait;
}
class AttendanceLogSchema implements \ModelInterface
{
    use AttendanceLogTrait;
    public function get_schema(){
        /// to be continue when available time

        $this->id = new \ModelFieldDesigner("id",0,(new \ModelFieldType())->integer,"primary","0");
        $this->uid = new \ModelFieldDesigner("uid",0,(new \ModelFieldType())->integer,"","0");
        $this->state = new \ModelFieldDesigner("state","100",(new \ModelFieldType())->varchar,"",null);
        $this->time = new \ModelFieldDesigner("time","",(new \ModelFieldType())->datetime,"","'0000-00-00 00:00:00'");
        $this->sync = new \ModelFieldDesigner("sync",0,(new \ModelFieldType())->integer,"","0");
        return get_object_vars($this);

    }
    public function table_name()
    {
        // TODO: Implement table_name() method.
        return "attendance_logs";
    }
}

class AttendanceLogView extends AttendanceLog {
    public function __construct()
    {
        $this->id = "attendance_logs.id";
        $this->uid = "attendance_logs.uid";
        $this->time = "attendance_logs.time";
        $this->state = "attendance_logs.state";
        $this->sync = "attendance_logs.sync";

    }
}

class AttendanceLogExportListView extends AttendanceLog {
    public $name = "users.name";
    public function __construct()
    {
        $this->uid = "attendance_logs.uid";
        $this->time = "attendance_logs.time";
        $this->state = "attendance_logs.state";
        $this->sync = "attendance_logs.sync";

    }
}

