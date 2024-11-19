<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:21 PM
 */

class ServerManagement
{
    public $vesta_hostname = "digitcare.click";
    public $vesta_user_name = "admin";
    public $vesta_login_user_name = "admin";
    public $vesta_login_password = "Y9wy8GxvEH";
    public $error_messages = array();
    public function __construct()
    {
        if (file_exists(PROJECT_ROOT.'.env')){
            $arr = M1\Env\Parser::parse(file_get_contents(PROJECT_ROOT.'.env'));
            if(isset($arr['VESTA_HOST_NAME'])){
                $this->vesta_hostname = $arr['VESTA_HOST_NAME'] ?? '';
                $this->vesta_login_user_name = $arr['VESTA_USER_NAME'] ?? '';
                $this->vesta_login_password = $arr['VESTA_PASSWORD'] ?? '';

            }
        }
        $this->error_messages = array(
            "Command has been successfuly performed",
            "Not enough arguments provided",
            "Object or argument is not valid",
            "Object doesn't exist",
            "Object already exists",
            "Object is suspended",
            "Object is already unsuspended",
            "Object can't be deleted because is used by the other object",
            "Object cannot be created because of hosting package limits",
            "Wrong password",
            "Object cannot be accessed be the user",
            "Subsystem is disabled",
            "Configuration is broken",
            "Not enough disk space to complete the action",
            "Server is to busy to complete the action",
            "Connection failed. Host is unreachable",
            "FTP server is not responding",
            "Database server is not responding",
            "RRDtool failed to update the database",
            "Update operation failed",
            "Service restart failed",
        );
    }



    function check_and_throw_vesta_exception($code,$success_codes=array(0)){
        if (!in_array($code, $success_codes))
        {
            throw new Exception($this->error_messages[$code]);
        }
    }

    function vesta_command_execute($vst_command,$arguments_data,$data_response=false){
        // Prepare POST query
        $postvars = array_merge(array(
            'user' => $this->vesta_login_user_name,
            'password' => $this->vesta_login_password,
            'returncode' => $data_response ? 'no' : 'yes',
            'cmd' => $vst_command,
            'arg1' => $this->vesta_user_name,
        ),$arguments_data);

        $postdata = http_build_query($postvars);

        // Send POST query via cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://' . $this->vesta_hostname . ':8083/api/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        return curl_exec($curl);

    }
    function cron_list(){
        $answer = $this->vesta_command_execute('v-list-cron-jobs',array(
            'arg2' => 'json' // THIS LINE OPTIONAL
        ),true);
        return json_decode($answer,true);

    }

    function cron_add($cron_file,$time=array()){
        $result = false;
        $array_instance = new ArrayAction();
        // Define schedule
        $default_time = array_merge(array(
            "minute" => "*",
            "hour" => "*",
            "month" => "*",
            "wday" => "*",
            "day" => "*",
        ),$time);
        $cmd = $cron_file;

        $cron_list = $this->cron_list();

        $filter = $array_instance->query_in_array($cron_list,array(
            "CMD" => $cmd
        ),false);

        if ($filter){
            $result = true;
        }
        else{
            $answer = $this->vesta_command_execute('v-add-cron-job',array(
                'arg2' => $default_time['minute'],
                'arg3' => $default_time['hour'],
                'arg4' => $default_time['day'],
                'arg5' => $default_time['month'],
                'arg6' => $default_time['wday'],
                'arg7' => $cmd
            ));

            // Check result
            if($answer == 0) {
                $result = true;
            }
            else{
                throw new Exception("Vesta cron add return error code ".$answer);
            }

        }

        return $result;

    }
    function cron_delete($cron_file){
        $result = false;
        $array_instance = new ArrayAction();
        $cmd = $cron_file;

        $cron_list = $this->cron_list();

        $filter = $array_instance->query_in_array($cron_list,array(
            "CMD" => $cmd
        ),false);

        if (!$filter){
            $result = true;
        }
        else{
            $answer = $this->vesta_command_execute('v-delete-cron-job',array(
                'arg2' => $filter['JOB']        // WHICH CRONJOB DO YOU WANT TO DELETE? CRONJOB ID COMES HERE.
            ));
            // Check result
            if($answer == 0) {
                $result = true;
            }
            else{
                throw new Exception("Vesta cron delete return error code ".$answer);
            }

        }

        return $result;

    }

    function add_domain($domain){

        $answer = $this->vesta_command_execute('v-add-web-domain',array(
            'arg2' => $domain
        ));
        // Check result
        $this->check_and_throw_vesta_exception($answer,array(0,4));
        return true;

    }


    function delete_domain($domain){

        $answer = $this->vesta_command_execute('v-delete-web-domain',array(
            'arg2' => $domain
        ));
        // Check result
        $this->check_and_throw_vesta_exception($answer,array(0,3));
        return true;

    }


    function delete_domain_ssl($domain){
        $answer = $this->vesta_command_execute('v-delete-letsencrypt-domain',array(
            'arg2' => $domain
        ));
        // Check result
        $this->check_and_throw_vesta_exception($answer,array(0,3));
        return true;

    }



    function delete_database($database){

        $answer = $this->vesta_command_execute('v-delete-database',array(
            'arg2' => $database
        ));
        // Check result
        $this->check_and_throw_vesta_exception($answer,array(0,3));
        return true;

    }

    function delete_pwa_config($customer_id){
        $sw_config_file = PROJECT_ROOT.$customer_id."-sw.js";
        $manifest_config_file = PROJECT_ROOT.$customer_id."-manifest.json";
        if (file_exists($sw_config_file)){
            unlink($sw_config_file);
        }

        if (file_exists($manifest_config_file)){
            unlink($manifest_config_file);
        }

        $pwa_config_instance = new PWAConfiguration($customer_id);
        if (file_exists(PROJECT_ROOT.$pwa_config_instance->pwa_config_file)){
            unlink(PROJECT_ROOT.$pwa_config_instance->pwa_config_file);
        }

        return true;

    }




    function add_domain_ssl($domain){

        if ($this->is_domain_ssl_active($domain)){
            return true;
        }

        /// add lest entry for https or ssl certificate add
        $answer = $this->vesta_command_execute('v-add-letsencrypt-domain',array(
            'arg2' => $domain
        ));
        // Check result
        $this->check_and_throw_vesta_exception($answer,array(0,4));

        if (!$this->is_domain_ssl_active($domain)){
            throw new Exception("Lets encrypt status is no");
        }

        return true;

    }

    function is_domain_ssl_active($domain){
        $answer = $this->vesta_command_execute('v-list-web-domain',array(
            'arg2' => $domain,
            'arg3' => 'json',
        ),true);
        $response = json_decode($answer,true);
        $domain_info = $response[$domain] ?? array();
        $ssl = $domain_info['LETSENCRYPT'] ?? 'no';
        if ($ssl == 'no'){
            return 0;
        }
        else{
            return 1;
        }


    }

    function add_db(){
        $string_instance = new StringAction();
        $db_info = array(
            'host_name' => 'localhost',//hostname
            'db_name' => $string_instance->generateRandomString(10),//db name
            'user_name' => $string_instance->generateRandomString(10),//username
            'password' => $string_instance->generateRandomString(15),//password
        );
        $answer = $this->vesta_command_execute('v-add-database',array(
            'arg2' => $db_info['db_name'],//db name
            'arg3' => $db_info['user_name'],//username
            'arg4' => $db_info['password'],//password
        ));

        // Check result
        $this->check_and_throw_vesta_exception($answer);

        $db_info['db_name'] = $this->vesta_user_name."_".$db_info['db_name'];
        $db_info['user_name'] = $this->vesta_user_name."_".$db_info['user_name'];
        return $db_info;

    }

    function import_db($db_info){
        try {
            $db_engine = "mysql";
            $db_host = $db_info['host_name'];
            $db_name = $db_info['db_name'];
            $db_user_name = $db_info['user_name'];
            $db_password = $db_info['password'];
            $dump = new Rah\Danpu\Dump;
            $dump
                ->file(PROJECT_ROOT.'initializer/export/database/development.sql')
                ->dsn("$db_engine:dbname=$db_name;host=$db_host")
                ->user($db_user_name)
                ->pass($db_password)
                ->tmp($this->tmp_directory());

            new Rah\Danpu\Import($dump);
        } catch (Exception $e) {
            throw new Exception('Import failed with message: ' . $e->getMessage());
        }
        return true;
    }

    function import_chart_of_accounts(){
        (new ExportAccountsRequirement())->export();
        $import_instance = new ImportAccountsRequirement();
        $import_instance->file = PROJECT_ROOT.'uploads/export/chart-of-accounts/accounts-requirement.sql';
        $import_instance->clear_before_data();
        $import_instance->save_new_data();

        return true;
    }

    function app_assets_clone($domain,$server_domain){
        $output=null;
        $retval=null;
        $command = "
        rm -rf /home/admin/web/$domain/public_html/{,.[!.],..?}*;
        rm -rf /home/admin/web/$domain/public_html;
        ln -s /home/admin/web/$server_domain/public_html /home/admin/web/$domain
        
        ";
        exec($command, $output, $retval);

        // Check result
        if($retval != 0) {
            throw new Exception("Query returned error code: " .$retval);
        }
        return true;

    }



    function tmp_directory(){
        return ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();

    }


    function cpanel_info(){
        return array(
            "domain" => "salamnbrothers.com",
            "user" => "salam2brother",
            "password" => "J[m%zn?yyL!u",
        );
    }
}