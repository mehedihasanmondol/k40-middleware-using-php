<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/20/2021
 * Time: 11:37 AM
 */

class ReportAction
{
    public function amount_collector($items){
        $amount = 0;
        foreach ($items as $item){
            $amount += $item['amount'];
        }

        return $amount;
    }

    function make_peity_visitor_data($rows_data){
        $debugger = new Debugger();
        $debugger->data = array();
        $debugger->add_property('total',0);
        $dates = array();
        $total = 0;
        foreach ($rows_data as $item){
            $dates[] = strtotime($item['date']);
            $total += $item['amount'];
        }

        $dates = array_unique($dates);

        $debugger->add_property('total',$total);
        $debugger->data = join(",",$dates);

        $debugger->status = 1;

        return $debugger;
    }
}