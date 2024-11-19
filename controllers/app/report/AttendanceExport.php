<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 11/2/2021
 * Time: 9:44 AM
 */

class AttendanceExport
{
    public $assistant;

    public function __construct($connection = null)
    {
        $this->assistant = new CommonAssistant($connection);
    }


    public function export(){
        $debugger = new Debugger();

        try{

            $setting_instance = new Setting($this->assistant->crud->connection);
            $setting_instance->update_property();
            $company_name = $setting_instance->company_name;
            $report_info = "<h4>$company_name</h4>";
            $report_info_array = array($company_name);

            $debugger->add_property('report_info',$report_info);

            $footer_text = "";
            $footer_text .= "Generated ";
            $footer_text .= " - on ".date("M d,Y H:i:s A");
            $debugger->add_property('print_time',$footer_text);


            $debugger->data = (new AttendanceLog($this->assistant->crud->connection))->attendance_export_list();

            $excel_rows = array();
            if ($debugger->data){
                $excel_file_instance = new ExcelFile($this->assistant->crud->connection);
                $excel_rows[] = array("simple_row" => "Attendance report");
                foreach ($report_info_array as $line){
                    $excel_rows[] = array("simple_row" => $line);
                }
                $excel_rows[] = $debugger->data;

                try{
                    $result = $excel_file_instance->excel_file_maker_by_group($excel_rows,"Attendance report");
                    if ($result->status){
                        $debugger = $result;

                    }

                }catch (Exception $exception){
                    $debugger->add_error($exception->getMessage());
                }
            }
            else{
                $debugger->add_message("Data not found");
            }

            if (!$debugger->issue_found()){
                $debugger->status = 1;
            }

        }catch (Exception $exception){
            $debugger->add_error($exception->getMessage());
        }


        return $debugger;
    }
}