<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/24/2021
 * Time: 11:49 AM
 */

class QueryBuilder extends ArrayQuery\QueryBuilder
{
    public function getFirstResult()
    {
        $results = array_values($this->getResults());
        return $results[0] ?? [];
    }
    public function getLastResult()
    {
        $results = array_values($this->getResults());
        $count = count($results);

        return $results[$count-1] ?? [];
    }
}