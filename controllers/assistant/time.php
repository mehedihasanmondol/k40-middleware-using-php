<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:32 PM
 */

class Time
{
    function date_difference($start_date,$end_date,$format="Minute %i : Seconds %s"){
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);
        $day_difference = $end_date->diff($start_date)->format($format);
        return $day_difference;
    }
    function next_recurring_date($start_date,$current_date,$days_of_period){
        $day_difference = $this->date_difference($start_date,$current_date);
        $range = ceil($day_difference / $days_of_period);
        if (!$range){
            $range = 1;
        }
        $start_date = $start_date." "."00:00:00";
        $next_date = "0000-00-00";
        for ($i = 1; $i <= $range; $i++){
            $next_date = $this->next_time($days_of_period,0,0,"Y-m-d",$start_date);
            $start_date = $next_date." "."00:00:00";
        }
        return $next_date;
    }
    function current_time_stamp(){
        return date("Y-m-d H:i:s");
    }
    function am_pm_time_format($time){
        $result = 0;
        $explode = explode(" ",$time);
        if (count($explode) == 2){
            $result = $time;
        }
        else{
            $default_format = date("Y-m-d ".$time.":00");
            $result = $this->text_date_time("h:i A",$default_format);
        }
        return  $result;
    }
    function first_date_of_date($date){
        $d = new DateTime($date);
        $d->modify('first day of this month');
        return $d->format('Y-m-d');
    }
    function last_date_of_date($date){
        $d = new DateTime($date);
        $d->modify('last day of this month');
        return $d->format('Y-m-d');
    }
    function next_time($day=0,$hour=0,$minute=0,$format="Y-m-d H:i:s",$timestamp=null,$second=0){
        if ($timestamp){
            $explode = explode(" ",$timestamp);
            $date = $explode[0];
            $time = $explode[1];
            $explode_date = explode("-",$date);
            $year = $explode_date[0];
            $month = $explode_date[1];
            $day = $explode_date[2] + $day;
            $explode_time = explode(":",$time);
            $hour = $explode_time[0] + $hour;
            $minute = $explode_time[1] + $minute;
            $second = $explode_time[2] + $second;
            $next_time = date($format,mktime($hour,$minute,$second,$month,$day,$year));
            return $next_time;
        }


        $next_time=date($format,strtotime("+$day days  $hour hours $minute minutes $second seconds "));
        return $next_time;
    }
    function due_date($issue_date,$due_day){
        $iss_date=explode("-",$issue_date);
        $d=$iss_date[2];
        $m=$iss_date[1];
        $y=$iss_date[0];
        $time=mktime(0,0,0,$m,$d,$y);
        $due_date=date("Y-m-d",strtotime("+$due_day days",$time));
        return $due_date;
    }
    function text_date_time($format="",$date_time=""){
        if (!$date_time){
            $date_time = date("Y-m-d H:i:s");
        }
        if (!$format){
            $format = "j M, Y g:i A";
        }
        return date($format, strtotime($date_time));
    }
    function age_calculator($birth_date,$year=true,$month=true,$day=true){
        $age = $this->date_difference($birth_date,date("Y-m-d"),"%y,%m,%d");
        $age_data = array();
        $age_parts = explode(",",$age);
        if ($age_parts[0]){
            if ($year){
                $age_data[] = $age_parts[0]." Year ";
            }

        }
        if ($age_parts[1]){
            if ($month){
                $age_data[] = $age_parts[1]." Month";
            }

        }
        if ($age_parts[2]){
            if ($day){
                $age_data[] = $age_parts[2]." Day ";
            }

        }
        return  join(" ",$age_data);
    }
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    function time_convert_by_zone($time, $tz = 'UTC')
    {
        // create a $dt object with the default timezone
        $dt = new DateTime($time, new DateTimeZone(date_default_timezone_get()));

        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone($tz));

        // format the datetime
        return $dt->format('Y-m-d H:i:s');
    }

}