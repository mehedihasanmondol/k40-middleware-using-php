<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/28/2021
 * Time: 2:04 PM
 */

namespace CustomizeRules;


use Rakit\Validation\Rule;

class GreaterThan extends Rule
{
    /** @var string */
    protected $message = "The :attribute must be greater than :greater_than";

    /** @var array */
    protected $fillableParams = ['greater_than'];

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

        $conditional_value = $this->parameter('greater_than');

        if ($value <= $conditional_value) {
            $result = false;
        }

        return $result;
    }
}