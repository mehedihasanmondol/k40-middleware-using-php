<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 6:07 PM
 */

class Debugger
{
    public $errors = array();
    public $messages = array();
    public $data = array();
    public $status = 0;
    function error_in_object($object_data){
        $result = false;
        if ($object_data['errors'] or $object_data['messages']){
            $result = true;
        }
        return $result;
    }
    function conclusion(){
        return get_object_vars($this);
    }
    function add_error($error){
        $this->errors[] = $error;
    }
    function add_message($message){
        $this->messages[] = $message;
    }
    function merge_issues_from_other($debugger_class){
        $this->errors = array_merge($this->errors,$debugger_class->errors);
        $this->messages = array_merge($this->messages,$debugger_class->messages);
    }

    function add_property($key,$value){
        $this->$key = $value;
    }


    function issue_found(){
        $result = false;
        if ($this->errors or $this->messages){
            $result = true;
        }
        return $result;
    }



}