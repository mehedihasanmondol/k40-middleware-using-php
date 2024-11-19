<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/7/2021
 * Time: 1:55 PM
 */

class TableDataDecoder
{
    public $number_params = array();
    public $json_params = array();
    public function __construct()
    {
        $this->number_params = array(

        );
        $this->json_params = array(

        );

    }

    function settings_merge($new_db_settings_result,$default_settings=array()){
        $array_action = new ArrayAction();
        $software_info = $default_settings;

        foreach ($default_settings as $key => $value){
            $filter_keys = array(
                "type" => "software_".$key
            );
            $filter = $array_action->filter_in_array($new_db_settings_result,$filter_keys,false);
            if($filter){
                if(strlen($array_action->index_number_in_list($this->number_params,$key))){
                    $software_info[$key] = floatval($filter['value']);
                }
                elseif(strlen($array_action->index_number_in_list($this->json_params,$key))){
                    $software_info[$key] = array_merge($value,json_decode($filter['value'],true));
                }
                else{
                    $software_info[$key] = $filter['value'];
                }

            }

        }

        return $software_info;
    }
}