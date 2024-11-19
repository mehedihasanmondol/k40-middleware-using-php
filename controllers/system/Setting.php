<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 7:24 PM
 */

class Setting
{
    public $model;
    public $assistant;

    use \System\Setting\Model\SettingTrait;

    public $appVersion = 0.1;
    public $assigned = 0;

    public $title = "ZKT offline middleware | digitCare";
    public $time_zone = "Asia/Dhaka";
    public $time_format = "d m Y";
    public $logo = "static/app/assets/img/default/no-image.png";
    public $favicon = "favicon.ico";
    public $email = "info@page71.org";
    public $company_name = "digitCare 01312345653 ";
    public $ip = "192.168.0.201";
    public $port = 4370;
    public $protocol = "TCP";
//    public $server = "http://school-management.com";
    public $server = "https://example-att-server.com";



    function __construct($connection=null)
    {
        $this->model = new System\Setting\Model\Setting();
        $this->assistant = new CommonAssistant($connection);


    }

    function update_property(){
        $default_setting = $this->get_setting();
        return $this->property_updater($default_setting);
    }

    function property_updater($default_setting){
        $query_labels = array();
        foreach ($default_setting as $key => $value){
            $query_labels[] = "software_".$key;
        }


        $sql = "where type in(".$this->assistant->crud->sql_in_maker($query_labels).")";
        $sql_data = array(
        );

        $columns = $this->assistant->crud->columns_by_model(new System\Setting\Model\SettingView);
        $find = $this->assistant->crud->retriever("settings",$columns,$sql,$sql_data,true);

        $decoder = new TableDataDecoder();
        $new_setting = $decoder->settings_merge($find,$default_setting);
        foreach ($new_setting as $key => $value){
            $this->$key = $value;
        }
        return true;
    }



    function get_setting(){
        return $this->assistant->crud->object_vars_by_model(new System\Setting\Model\SettingPropertyView(),$this);
    }

    function get_all_setting(){
        return array_merge(
            $this->assistant->crud->object_vars_by_model(new System\Setting\Model\SettingPropertyView(),$this)
        );
    }


    public function create(){
        return $this->assistant->crud->creator("settings",$this->assistant->crud->prepare_insert_data($this->model,$this));
    }

    public function update(){

        $array_action = new ArrayAction();
        $data_decoder = new TableDataDecoder();
        $setting_type = str_replace("software_","",$this->type);
        if(strlen($array_action->index_number_in_list($data_decoder->json_params,$setting_type))){
            $method_name = 'update_'.$setting_type.'_property';
            if (method_exists($this,$method_name)){
                $this->$method_name();
            }

            $current_value = json_decode($this->value,true);
            $default_value = $this->$setting_type;
            $this->value = json_encode(array_merge($default_value,$current_value));
        }

        $sql = "where type=:type ";
        return $this->assistant->crud->modifier("settings",$sql,array(
            'updates' => array(
                "value" => $this->value
            ),
            "condition_data" => array(
                ":type" => $this->type
            )
        ));
    }

    public function update_properties($type){
        $columns = $this->assistant->crud->columns_by_model(new System\Setting\Model\SettingView());
        $sql = "where type = :type  ";
        $data = array(
            ":type" => $type,
        );

        $result = $this->assistant->crud->retriever("settings",$columns,$sql,$data);
        foreach ($result as $key => $value){
            $this->$key = $value;
        }

        if ($result){
            $this->assigned = 1;
        }
        else{
            $this->assigned = 0;
            $this->type = $type;
        }
        return true;

    }

    public function assign(){
        $result = false;
        if ($this->assigned){
            $result = $this->update();
        }
        else{
            $result = $this->create();
        }
        return $result;
    }

    public function form_data_validation($update_rules=array()){
        $debugger_instance = new Debugger();
        $validation_instance = new DataValidator();
        $rules = [
            'type'                  => 'required',
        ];

        $rules = array_merge($rules,$update_rules);


        $validation = $validation_instance->validate($rules);
        if (!$validation->status){
            $debugger_instance->messages = $validation->messages;
        }
        else{
            $debugger_instance->status = 1;
        }

        return $debugger_instance;
    }

    public function form_data_collection(){
        $this->type = $_REQUEST['type'] ?? $this->type;
        $this->value = $_REQUEST['value'] ?? $this->value;
        return true;
    }


    public function update_from_request($by_user=false){
        $debugger_instance = new Debugger();
        try{
            //প্রথমে  validation করতে হবে। step 1
            $validation_result = $this->form_data_validation(array(
                "password" => "min:8"
            ));
            if (!$validation_result->status){
                $debugger_instance->merge_issues_from_other($validation_result);
                return $debugger_instance;
            }

            // যেহেতু edit request তাই Form data এর মধ্যে type field আছে
            // Default value দিয়ে দিলাম

            $this->update_properties($_REQUEST['type'] ?? $this->type);

            /// এরপর ফর্ম এর ডাটা কালেক্ট করে নিতে হবে। step 2
            $this->form_data_collection();

            if ($debugger_instance->issue_found()){
                return $debugger_instance;
            }

            new MysqlTransaction(function ($transaction) use(&$debugger_instance){
                $this->assistant->crud->connection = $transaction->connection;
                $this->assign();

                if (!$debugger_instance->issue_found()){
                    $debugger_instance->add_message('updated');
                    $debugger_instance->status = 1;
                    $transaction->status = 1;
                }

            },function ($transaction) use(&$debugger_instance){/// on error call back
                $debugger_instance->add_error($transaction->error);
            },$this->assistant->crud->connection);


        }catch (Exception $exception){
            $debugger_instance->add_error($exception->getMessage());
        }

        return $debugger_instance;
    }


}