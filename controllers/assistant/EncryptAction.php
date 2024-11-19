<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:51 PM
 */

class EncryptAction
{
    public $encrypt_string = "digitCare";
    public function encrypt($text){
        $text = (new StringAction())->make_key_of_string($text);
        $new_text = strlen($text).$text.$this->encrypt_string;
        return $new_text;
    }

}