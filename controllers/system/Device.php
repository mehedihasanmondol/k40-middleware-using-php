<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 7:24 PM
 */
error_reporting(0);
class Device
{
    public $instance;

    function __construct($connection=null,ZKLibrary $device_instance=null)
    {
        if (!$device_instance){
            $settings = new Setting($connection);
            $settings->update_property();
            $this->instance = new ZKLibrary($settings->ip,$settings->port,$settings->protocol);

            $linger     = array ('l_linger' => 0, 'l_onoff' => 1);
            socket_set_option($this->instance->socket, SOL_SOCKET, SO_LINGER, $linger);

            $this->instance->connect();
        }
        else{
            $this->instance = $device_instance;
        }
    }

}