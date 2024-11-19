<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 11:36 AM
 */

class Server
{
    public $server_address = "";
    function __construct()
    {
        $this->server_address = $_SERVER['SERVER_ADDR'] ?? '';
        if (!$this->server_address){
            $this->server_address = gethostbyname(gethostname());

        }
    }

    function is_localhost(){
        $result = false;
        if ($this->server_address == "127.0.0.1" or $this->server_address == "::1" ){
            $result = true;
        }
        else{
            $explode_server_address = explode(".",$this->server_address);
            if (count($explode_server_address) >= 2){
                if ($explode_server_address[0].".".$explode_server_address[1] == "192.168"){
                    $result = true;
                }
            }
        }


        return $result;
    }
    function is_online_host(){
        $result = false;
        if (!$this->is_localhost()){
            $result = true;
        }
        return $result;
    }
    function site_protocol(){
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
//        if ($protocol == "http" and $this->is_online_host()){
//            $protocol = "https";
//        }
        return $protocol;
    }
    function site_port(){
        $port = 80;
        if (isset($_SERVER['SERVER_PORT'])){
            $port = $_SERVER['SERVER_PORT'];
        }

        return $port;
    }



}