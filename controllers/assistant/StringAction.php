<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:51 PM
 */

class StringAction
{
    function string_from_text($str,$multiple=false){
        $d = preg_match_all("/[^0-9]+(?<!:)(?<!\.)[^0-9]?/",$str,$matches);
        $data = $matches[0];
        if (!$multiple){
            if ($data){
                return $data[0];
            }
            else{
                return false;
            }
        }
        return $data;
    }
    function readable_text($string){
        $clean = preg_replace("/([-]+|[_]+)/"," ",$string);
        $first_character_upper = ucfirst($clean);
        return $first_character_upper;
    }
    function password_generator(){
        return rand(10000000,99999999);
    }
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function short_form_of_sentence($sentence){
        $result = "";
        foreach (explode(" ",$sentence) as $word){
            if ($word){
                $result .= strtoupper(substr(trim($word),0,1));
            }
        }
        return $result;
    }

    function make_key_of_string($string){
        return strtolower(preg_replace("/([-]+|[.]+|[ ]+)/","_",$string));

    }

    function sum_of_string_position($string){
        $array_instance = new ArrayAction();
        $characters = array(
            "a","b","c","d","e","d","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
            0,1,2,3,4,5,6,7,8,9,
            '','.','-','_');
        $total_position = 0;
        foreach (str_split($string) as $char) {
            $index = $array_instance->index_number_in_list($characters,$char);
            if (strlen($index)){
                $total_position += $index;
            }
        }
        return $total_position;

    }

}