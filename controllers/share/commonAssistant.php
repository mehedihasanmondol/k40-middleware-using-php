<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 6:15 PM
 */

class CommonAssistant extends ExecutionPrintAssistant
{
    public $crud;
    public $time;
    public $client;
    public $number;

    function __construct($connection=null)
    {
        $this->crud = new Crud($connection);
        $this->time = new Time();
        $this->client = new Client();
        $this->number = new NumberAction();
    }

}