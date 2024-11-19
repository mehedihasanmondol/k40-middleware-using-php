<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 4:56 PM
 */

class IdInformation
{
    function id_info($id,$info_type){
        $id_type = substr($id,0,3);
        $serial = substr($id,3,10);
        $user_row_id = substr($id,13,10);
        $device_row_id = substr($id,23,10);
        $option = substr($id,33,33);
        $self = $id_type.$serial.$user_row_id.$device_row_id;
        if ($info_type == "type"){
            return $id_type;
        }
        elseif ($info_type == "serial"){
            return $serial;
        }
        elseif ($info_type == "user_row_id"){
            return $user_row_id;
        }
        elseif ($info_type == "device_row_id"){
            return $device_row_id;
        }
        elseif ($info_type == "option"){
            return $option;
        }
        elseif ($info_type == "self"){
            return $self;
        }
        else{
            throw new Exception("Id info not found");
        }
    }
    function number_to_id($number){
        return str_pad($number,66,0,STR_PAD_RIGHT);
    }
    function type_name($type){
        $data=array_flip($this->id_type());
        return $data[$type] ?? '';
    }

    function id_type_name($user_id){
        try{
            $id_info=$this->id_info($user_id,"type");
        }catch (Exception $exception){
            return false;
        }
        return $type_name=$this->type_name($id_info);
    }
    function id_type($type=''){
        //for ensure last update type is available
        $data=array(
            "system_designer" => "000",
            "admin" =>"001",
            "document" =>"002",
            "parent" =>"003",
            "institute" =>"004",
            "teacher" =>"005",
            "student" =>"006",
            "guardian" =>"007",
            "monitoring_authority" =>"008",

        );
        if ($type){
            if (isset($data[$type])){
                return $data[$type];
            }else{
                return false;
            }
        }
        else{
            return $data;
        }

        /// option types maybe below
        //.. member_id,any user_id,
    }
    function member_id_prefix($type=""){
        $data = array(
            "system_designer" => "S",
            "admin" => 'A',
            "document" => 'D',
            "parent" => 'P',
            "institute" => 'I',
            "teacher" => 'T',
            "student" => 'ST',
            "guardian" => 'G',
            "monitoring_authority" => 'M',

        );

        if ($type){
            if (isset($data[$type])){
                $result = $data[$type];
                return $result;
            }
            else{
                return false;
            }
        }
        else{
            return $data;
        }
    }

    function id_type_option($type=''){
        //for ensure last update type is available
        $data=array(
            "invoice" => "1",
            "bill" => "2",
            "receipt" => "3",
            "service" => "4",
            "sms" => "5",
            "wallet" => "6",
        );
        if ($type){
            if (isset($data[$type])){
                return $data[$type];
            }else{
                return false;
            }
        }
        else{
            return $data;
        }

        /// option types maybe below
        //.. member_id,any user_id,
    }

}