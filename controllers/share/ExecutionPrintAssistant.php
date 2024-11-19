<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 6:15 PM
 */

class ExecutionPrintAssistant
{
    function print_line($value){
        print_r("<br>".$value);
    }
    function print_array($value){
        echo "<pre>";
        print_r($value);
        echo "</pre>";
    }
    function print_on_browser($value){
        echo $value."<br>";
    }
    function force_flush($string) {
        for ( $i = 0; $i < 15; $i++ )
            echo "<!-- ".str_pad('a',8000,'a')." -->\n\n";

        while ( ob_get_level() )
            ob_end_flush();

        echo $string;

        @ob_flush();
        @flush();
    }

    function send_buffer_on_browser($buffer){
        $this->force_flush($buffer."<br>");
        sleep(1);
    }



}