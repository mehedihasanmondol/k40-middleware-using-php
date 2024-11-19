<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:42 PM
 */

class ArrayAction
{
    function aasort (&$array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
    }
    function aasort_by_order ($array,$key,$order="asc") {
        $this->aasort($array,$key);
        if ($order == "desc"){
            $array = array_reverse($array);
        }
        return $array;
    }
    function custom_filter($array, $column, $key){
        return (array_search($key, array_column($array, $column)));
    }
    function index_number_in_array($array, $column, $key){
        return $this->custom_filter($array,$column,$key);
    }
    function index_number_in_list($array, $key){
        return $a = array_search($key, $array);
    }

    function filter_in_array($list,$filter_keys,$list_data=true,$negative=false,$original_index=false,$lower_string = false){
        $get_data = array_filter($list,function ($item) use($filter_keys,$list_data,$negative,$original_index,$lower_string){
            $key_count = count(array_keys($filter_keys));
            $true_count = 0;
            foreach ($filter_keys as $key => $value){
                if ($negative){
                    if (isset($item[$key])){
                        if ($lower_string){
                            if (gettype($item[$key]) == 'string'){
                                if (strtolower($item[$key]) != strtolower($value)){
                                    $true_count++;
                                }
                            }
                            else{
                                if ($item[$key] != $value){
                                    $true_count++;
                                }
                            }
                        }
                        else{
                            if ($item[$key] != $value){
                                $true_count++;
                            }
                        }


                    }
                    else{
                        $true_count++;
                    }

                }
                else{
                    if (isset($item[$key])){
                        if ($lower_string){
                            if (gettype($item[$key]) == 'string'){
                                if (strtolower($item[$key]) == strtolower($value)){
                                    $true_count++;
                                }
                            }
                            else{
                                if ($item[$key] == $value){
                                    $true_count++;
                                }
                            }
                        }
                        else{
                            if ($item[$key] == $value){
                                $true_count++;
                            }
                        }


                    }

                }

            }
            if ($key_count == $true_count){
                return true;
            }
            else{
                return false;
            }
        });

        if (!$list_data){
            foreach ($get_data as $item){
                return $item;
            }
        }

        if ($original_index){
            $list = $get_data;
        }
        else{
            $list = array();
            foreach($get_data as $item){
                $list[] = $item;
            }
        }
        return $list;
    }

    function query_in_array($array,$query,$multiple = false,$operator="="){
        ArrayQuery\Filters\CriterionFilter::$operatorsMap['='] = 'EqualFilter';
        $qb = QueryBuilder::create($array);
        foreach ($query as $key => $value){
            $qb->addCriterion($key,$value,$operator);

        }
        if ($multiple){
            return array_values($qb->getResults());
        }
        else{
            return $qb->getFirstResult();
        }
    }
    function array_list_reconstruction($list){
        $new_list = array();
        foreach ($list as $item){
            $new_list[] = $item;
        }
        return $new_list;
    }
    function merge_return_objects($object_data,$object_data_2){
        $status = $object_data['status'];
        $errors = array_merge($object_data['errors'],$object_data_2['errors']);
        $messages = array_merge($object_data['messages'],$object_data_2['messages']);
        $object_data = array_merge($object_data,$object_data_2);
        $object_data['status'] = $status;
        $object_data['errors'] = $errors;
        $object_data['messages'] = $messages;
        return $object_data;
    }
    function remove_empty_elements($array){
        return array_filter($array,function ($input){
            return $input;
        });
    }
}