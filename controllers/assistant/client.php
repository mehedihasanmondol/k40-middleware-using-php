<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:25 PM
 */

class Client
{
    public $serverAssistant;
    function __construct()
    {
        $this->serverAssistant = new Server();
    }

    function main_domain(){
        $server_name = "localhost";
        if (isset($_SERVER['SERVER_NAME'])){
            $server_name = $_SERVER['SERVER_NAME'];
        }


        if ($server_name == 'localhost' and isset($argv)){
            parse_str($argv[1],$params);
            $server_name=$params['domain'];
        }

        if ($server_name == 'localhost' and (new Server())->is_online_host()){
            $part_of_project_root = explode("/",rtrim(str_replace("\\","/",PROJECT_ROOT),'/'));
            $domain_name_folder = $part_of_project_root[count($part_of_project_root) - 2];
            if ($domain_name_folder){
                $server_name = $domain_name_folder;
            }
        }
        /// remove (www.) if available of this name string
        $server_name = preg_replace("/www\./","",$server_name,1);

        if($this->serverAssistant->site_port() != 80 && $this->serverAssistant->site_port() != 443){
            $server_name .= ":".$this->serverAssistant->site_port();
        }
        return $server_name;
    }

    function is_main_domain(){
        return true;
    }
    function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $pattern = "";
        if ($u_agent){
            //First get the platform?
            if (preg_match('/linux/i', $u_agent)) {
                $platform = 'linux';
            } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
                $platform = 'mac';
            } elseif (preg_match('/windows|win32/i', $u_agent)) {
                $platform = 'windows';
            }

            // Next get the name of the useragent yes seperately and for good reason
            if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
                $bname = 'Internet Explorer';
                $ub = "MSIE";
            } elseif (preg_match('/Firefox/i', $u_agent)) {
                $bname = 'Mozilla Firefox';
                $ub = "Firefox";
            } elseif (preg_match('/Chrome/i', $u_agent)) {
                $bname = 'Google Chrome';
                $ub = "Chrome";
            } elseif (preg_match('/Safari/i', $u_agent)) {
                $bname = 'Apple Safari';
                $ub = "Safari";
            } elseif (preg_match('/Opera/i', $u_agent)) {
                $bname = 'Opera';
                $ub = "Opera";
            } elseif (preg_match('/Netscape/i', $u_agent)) {
                $bname = 'Netscape';
                $ub = "Netscape";
            }
            else{
                $ub = "Unknown";
            }

            // finally get the correct version number
            $known = array('Version', $ub, 'other');
            $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (!preg_match_all($pattern, $u_agent, $matches)) {
                // we have no matching number just continue
            }

            // see how many we have
            $i = count($matches['browser']);
            if ($i != 1) {
                //we will have two since we are not using 'other' argument yet
                //see if version is before or after the name
                if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                    $version = $matches['version'][0];
                } else {
                    $version = $matches['version'][1];
                }
            } else {
                $version = $matches['version'][0];
            }

            // check if we have a number
            if ($version == null || $version == "") {
                $version = "?";
            }
        }



        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }
    function getRealIpAddr()
    {
        $unknown_ip = "0.0.0.0";
        if (!empty($_SERVER['HTTP_CLIENT_IP'] ?? '' ))   //check ip from share internet
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '' ))   //to check ip is pass from proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'] ?? $unknown_ip ;
        }
        return $ip;
    }
    function logged_user_id(){
        return $this->user_cookies_data()['logged_user_id'];
    }
    function logged_user_row_id(){
        return $this->user_cookies_data()['logged_user_row_id'];
    }
    function logged_user_device(){
        return $this->user_cookies_data()['device'];
    }

    function logged_branch_id(){
        $branch_id = "public";
        if (isset($_SESSION['logged_branch_id'])){
            $branch_id = $_SESSION['logged_branch_id'];
        }
        return $branch_id;
    }
    
    function logged_institute_id(){
        $institute_id = "not_set";
        if (isset($_SESSION['logged_institute_id'])){
            $institute_id = $_SESSION['logged_institute_id'];
        }
        return $institute_id;
    }
    

    function logged_customer_id(){
        $customer_id = "public";
        if (isset($_SESSION['logged_customer_id'])){
            $customer_id = $_SESSION['logged_customer_id'];
        }
        return $customer_id;
    }
    function user_cookies_data(){
        $return_object = array(
            "logged_user_id" => "",
            "device" => "",
            "logged_user_row_id" => "",
            "logged_branch_id" => "public",
            "logged_institute_id" => "not_set",
        );
        if (isset($_SESSION['logged_user_id'])){
            $return_object['logged_user_id'] = $_SESSION['logged_user_id'];
        }
        if (isset($_SESSION['device'])){
            $return_object['device'] = $_SESSION['device'];
        }
        if (isset($_SESSION['logged_user_row_id'])){
            $return_object['logged_user_row_id'] = $_SESSION['logged_user_row_id'];
        }
        if (isset($_SESSION['logged_branch_id'])){
            $return_object['logged_branch_id'] = $_SESSION['logged_branch_id'];
        }
        if (isset($_SESSION['logged_institute_id'])){
            $return_object['logged_institute_id'] = $_SESSION['logged_institute_id'];
        }
        


        return $return_object;
    }
    function set_logged_branch($branch_id){
        $_SESSION['logged_branch_id'] = $branch_id;
    }
    function set_logged_institute($institute_id){
        $_SESSION['logged_institute_id'] = $institute_id;
    }
    
    function set_logged_user($user_id){
        $_SESSION['logged_user_id'] = $user_id;
    }
    function set_logged_device($device_id){
        $_SESSION['device'] = $device_id;
    }
    function set_logged_user_row_id($row_id){
        $_SESSION['logged_user_row_id'] = $row_id;
    }
    function unset_logged_branch(){
        unset($_SESSION['logged_branch_id']);
    }
    function unset_logged_institute(){
        unset($_SESSION['logged_institute_id']);
    }
    
    function unset_logged_user(){
        unset($_SESSION['logged_user_id']);
    }
    function unset_logged_device(){
        unset($_SESSION['device']);
    }
    function unset_logged_user_row_id(){
        unset($_SESSION['logged_user_row_id']);
    }




    function logged_user_type(){
        $user_cookies = $this->user_cookies_data();
        $logged_user_id = $user_cookies['logged_user_id'];
        $id_generator = new IdInformation();
        if ($logged_user_id){
            return $id_generator->id_type_name($logged_user_id);
        }
        return '';

    }
}