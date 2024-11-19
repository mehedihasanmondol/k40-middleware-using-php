<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/28/2021
 * Time: 2:01 PM
 */
namespace CustomizeRules;
use Rakit\Validation\Rule;
class PositiveNumber extends Rule
{
    protected $message = "The :attribute must be positive number";

    public function check($value): bool
    {
        if (!$value){
            return true;
        }

        // true for valid, false for invalid
        return $value >= 0;
    }
}