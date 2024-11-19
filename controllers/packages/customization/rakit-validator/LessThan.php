<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/28/2021
 * Time: 2:05 PM
 */

namespace CustomizeRules;


use Rakit\Validation\Rule;

class LessThan extends Rule
{
    /** @var string */
    protected $message = "The :attribute must be less than :less_than";

    /** @var array */
    protected $fillableParams = ['less_than'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        $result = true;
        $this->requireParameters($this->fillableParams);

        $conditional_value = $this->parameter('less_than');
        if ($value >= $conditional_value) {
            $result = false;
        }

        return $result;
    }
}