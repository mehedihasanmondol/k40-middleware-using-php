<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/11/2021
 * Time: 5:15 PM
 */

class AttendanceLog
{
    public $model;
    public $assistant;

    use \App\AttendanceLog\Model\AttendanceLogTrait;

    public $assigned = 0;
    public $device_sn = '';
    public $settings;

    public function __construct($connection = null)
    {

        $this->model = new App\AttendanceLog\Model\AttendanceLog();
        $this->assistant = new CommonAssistant($connection);
        $this->settings = new Setting($connection);
        $this->settings->update_property();
        $this->device_sn = trim((new Device($connection))->instance->getSerialNumber());


    }

    public function update_properties($device_sn,$device_user_id,$time){
        $columns = $this->assistant->crud->columns_by_model(new App\AttendanceLog\Model\AttendanceLogView());
        $sql = "where device_sn = :device_sn and device_user_id = :device_user_id and time = :time";
        $data = array(
            ":device_sn" => $device_sn,
            ":device_user_id" => $device_user_id,
            ":time" => $time,
        );
        $result = $this->assistant->crud->retriever("attendance_logs",$columns,$sql,$data);

        foreach ($result as $key => $value){
            $this->$key = $value;
        }
        if ($result){
            $this->assigned = 1;
        }
        else{
            $this->assigned = 0;
        }
        return true;

    }

    public function get_attendance_log(){
        return $this->assistant->crud->object_vars_by_model(new App\AttendanceLog\Model\AttendanceLogView(),$this);
    }


    public function get_sinkable_log(){
        $columns = $this->assistant->crud->columns_by_model(new App\AttendanceLog\Model\AttendanceLogView());
        $sql = "where sync = :not_sync limit 100";
        $data = array(
            ":not_sync" => 0,
        );
        return $this->assistant->crud->retriever("attendance_logs",$columns,$sql,$data,true);

    }

    public function attendance_export_list(){
        $columns = $this->assistant->crud->columns_by_model(new App\AttendanceLog\Model\AttendanceLogExportListView());
        $sql = "left join users on users.uid = attendance_logs.uid ";
        $data = array(
        );
        return $this->assistant->crud->retriever("attendance_logs",$columns,$sql,$data,true);

    }


    public function create(){
        $result = $this->assistant->crud->creator("attendance_logs",$this->assistant->crud->prepare_insert_data($this->model,$this));
        $this->id = $this->assistant->crud->last_insert_id();
        return $result;
    }

    public function sync_on_server($watcher=false){
        $result = 0;
        ini_set('memory_limit', '-1');
        if ($watcher){
            $this->assistant->send_buffer_on_browser("Syncing on server");
        }

        while (true){
            $logs = $this->get_sinkable_log();

            if (!$logs){
                $result = 1;
                if ($watcher) {
                    $this->assistant->send_buffer_on_browser("All records saved on server. ");
                }
                break;

            }
            else{
                $result = 0;
                if ($watcher) {
                    $this->assistant->send_buffer_on_browser("New " . count($logs) . " records preparing....");
                }
                try{
                    $request_data = array();
                    foreach ($logs as $log){
                        $request_data[] = array(
                            "user_id" => $log['uid'],
                            "method" => "finger",
                            "check_time" => $log['time'],
                        );
                    }

                    // Prepare POST query
                    $postvars = array_merge(array(
                        'request_type' => 'attendance',
                        'machine_sn' => $this->device_sn,
                        'data' => json_encode($request_data)
                    ));

                    $postdata = http_build_query($postvars);

                    // Send POST query via cURL
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $this->settings->server.'/api/record/setAttendance.php');
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                    curl_setopt($curl, CURLOPT_FAILONERROR, true);
                    $raw_response = curl_exec($curl);

                    if ($watcher) {
                        $this->assistant->send_buffer_on_browser("New " . count($logs) . " records sends on server....");
                    }
                    if (curl_errno($curl)) {
                        $error_msg = curl_error($curl);
                        if ($watcher) {
                            $error_msg .= " <a href=''>Retry</a>";
                            $this->assistant->send_buffer_on_browser("cURL respond " . $error_msg);

                        }
                        break;
                    }

                    curl_close($curl);


                    $response = json_decode($raw_response,true);
                    $status = $response['status'] ?? 0;
                    if ($status){
                        foreach ($logs as $log){
                            $this->assistant->crud->modifier("attendance_logs","where id = :id",array(
                                "updates" => array(
                                    "sync" => 1
                                ),
                                "condition_data" => array(
                                    ":id" => $log['id']
                                )
                            ));
                        }
                        if ($watcher) {
                            $this->assistant->send_buffer_on_browser("New " . count($logs) . " records send successfully.");
                        }
                        $result = 1;
                    }
                    else{
                        if ($watcher) {
                            $this->assistant->send_buffer_on_browser($raw_response);
                        }
                    }
                }catch (Exception $exception){
                    if ($watcher) {
                        $this->assistant->send_buffer_on_browser($exception->getMessage());
                    }
                    break;
                }


            }


        }

        return $result;
    }

    public function save_data_from_device($watcher=false,$working_mode=false){
        ini_set('memory_limit', '-1');
        $status = 0;

        if ($watcher){
            $this->assistant->send_buffer_on_browser("Getting attendance logs from device...");

        }

        $attendance_data = (new Device($this->assistant->crud->connection))->instance->getAttendance();
        if ($attendance_data){
            if ($watcher) {
                $this->assistant->send_buffer_on_browser("New " . count($attendance_data) . " records saving in middleware... ");
            }
            try{
                $i = 1;
                foreach ($attendance_data as $key => $attendance){
//                    if ($watcher) {
//                        $this->assistant->send_buffer_on_browser("$i no. " ." record saving in middleware... ");
//                    }
                    $this->uid = $attendance[1];
                    if ($this->uid){
                        $this->state = $attendance[2];
                        $this->time = $attendance[3];
                        $this->sync = 0;
                        $this->create();
                    }
                    $i++;


                }
                if ($watcher) {
                    $this->assistant->send_buffer_on_browser("New " . count($attendance_data) . " records saved successfully in middleware... ");
                }

                (new Device($this->assistant->crud->connection))->instance->clearAttendance();
                if ($watcher) {
                    $this->assistant->send_buffer_on_browser("Clear device attendance logs");
                }
                $status = 1;
            }catch (Exception $exception){
                if ($watcher) {
                    $this->assistant->send_buffer_on_browser($exception->getMessage());
                }
            }

        }
        else{
            if ($watcher) {
                $this->assistant->send_buffer_on_browser("Don't have any new attendance records. ");
            }
        }

        return $status;
    }

}