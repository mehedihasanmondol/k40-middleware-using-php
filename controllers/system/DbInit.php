<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 7:24 PM
 */

class DbInit
{

    public function execute(){

        $debugger = new Debugger();
        try{
            $models = array(
                (new \App\AttendanceLog\Model\AttendanceLogSchema()),
                (new \App\User\Model\UserSchema()),
                (new \System\Setting\Model\SettingSchema())
            );
            $commands = array();

            foreach ($models as $model){

                $column_string_lines = array();
                $schema = $model->get_schema();

                $indexes = array();

                foreach ($schema as $key => $column_schema){
                    $column_string = $key." ".$column_schema->type;
                    if ($column_schema->length){
                        $column_string .= "(".$column_schema->length.")";
                    }

                    $column_string .= " NOT NULL ";

                    if ($column_schema->index == "primary"){
                        $column_string .= " PRIMARY KEY "." AUTOINCREMENT  ";
                    }
                    else{
                        if ($column_schema->index){
                            $indexes[$key] = $column_schema->index;
                        }
                        if ($column_schema->default != null){
                            $column_string .= " DEFAULT ".$column_schema->default;
                        }
                    }

                    $column_string_lines[] = $column_string;
                }

                $commands[] = " CREATE TABLE IF NOT EXISTS ".$model->table_name()." (\n".join(",\n",$column_string_lines)."\n);";

                if ($indexes){
                    foreach ($indexes as $column => $index_key){
                        $commands[] = " CREATE INDEX ".$index_key." ON ".$model->table_name()." (".$column."); ";
                    }

                }
            }

            $assistant = new CommonAssistant();
            $update_status = $assistant->crud->connection->exec(" ".join("\n",$commands)." ");



            $debugger->status = 1;
        }catch (Exception $exception){
            $debugger->add_error($exception->getMessage());
        }

        return $debugger;
    }

}