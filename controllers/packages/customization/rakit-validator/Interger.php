<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/28/2021
 * Time: 2:03 PM
 */

namespace CustomizeRules;


use Rakit\Validation\Rule;

class Interger extends Rule
{
    protected $message = "The :attribute must be integer";

    public function check($value): bool
    {
        $match = preg_match('/\D/',$value);
        if (!$match){
            $value = intval($value);

        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}