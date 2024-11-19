<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:47 PM
 */

class NumberAction
{
    function number_from_text($str,$multiple=false){
        $d = preg_match_all("/-?\d+\.?\:?\d*/",$str,$matches);
        $data = $matches[0];
        if (!$multiple){
            if ($data){
                return $data[0];
            }
            else{
                return false;
            }
        }
        return $data;
    }

    function percent_or_flat_amount($percent,$total,$type="flat"){
        if ($type == "flat"){
            return $percent;
        }
        else{
            return $this->amount(($total*$percent)/100);
        }

    }
    function get_int($string){
        return preg_replace('/\D/', '', $string);
    }

    function amount($amount,$decimal_digit_required=0,$separator=""){

        if (!$amount){
            $amount = 0;
        }

        $amount = number_format($amount,2,'.',$separator);
        if (!$decimal_digit_required){
            $amount += 0 ;
        }

        return $amount;

    }

    function number($amount){
        $number = floatval($amount);
        return $number;
    }
    function is_divisional($divisional_amount, $division_by){
        $result = false;
        if ($this->number($division_by)){
            $result = true;
        }
        return $result;
    }
    function negative_number($number){
        return $number - ($number + $number);
    }

    function positive_number($number){
        return abs($number);
    }

    function number_conversion ($number_str,$type){
        $EnglishToBanglaNumber= array("1" =>"১","2" =>"২","3" =>"৩","4" =>"৪","5" =>"৫","6" =>"৬","7" =>"৭","8" =>"৮","9" =>"৯","0" =>"০","%"=>"%");
        $BanglaToEnglishNumber=array_flip($EnglishToBanglaNumber);
        $array = array();
        if($type=="b2e"){
            $array= $BanglaToEnglishNumber;
        }
        elseif($type=="e2b"){
            $array= $EnglishToBanglaNumber;
        }
        elseif($type=="e2e"){
            $array=array();
        }
        $data=$number_str;
        foreach($array as $key=>$number){
            $data=str_replace($key,$number,$data);
        }
        return $data;
    }
    function round($amount){
        $result = array(
            "amount" => 0,
            "round_amount" => 0
        );
        $result['amount'] = floor($amount);
        $result['round_amount'] = $this->amount($amount - floor($amount));;
        return $result;
    }
    function convertNumberToWordEn($num = false)
    {
        $num = str_replace(array(',', ' '), '' , trim($num));

        if(! $num) {
            return "Zero";
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
            'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode(' ', $words)." only";
    }

    function convertNumberToWord($number){
        $result = $number;
        $bangla_switch = $this->bangla_switch();
        $retrieve = new retrieve($this->connection);
        $software_info = $retrieve->software_info();
        $currency = $software_info['currency'];
        if ($bangla_switch){
            $bangla_word_converter = new BanglaNumberToWord();
            $amount_as_words = $bangla_word_converter->numToWord($number);
            $result = $amount_as_words." ".$this->currency_symbol_name($currency,"bn");
        }
        else{
            $amount_as_words = $this->convertNumberToWordEn($number);
            $result = trim($amount_as_words.$this->currency_symbol_name($currency,"en"));
        }
        return $result;

    }
}