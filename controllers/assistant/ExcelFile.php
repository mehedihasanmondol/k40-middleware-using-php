<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 10/8/2021
 * Time: 11:06 AM
 */

class ExcelFile
{
    public $assistant;

    public function __construct($connection = null)
    {

        $this->assistant = new CommonAssistant($connection);
    }

    function excel_file_maker_by_group($data,$type=""){
        $debugger = new Debugger();
        $string_instance = new StringAction();
        $file_instance = new FileAction();
        try{
            if ($data){
                $onesheet = new \OneSheet\ExcelWriter();

                $boldHeader = (new OneSheet\Style\Style())->setFontBold();

                foreach ($data as $group_index => $group_rows){
                    if (isset($group_rows['simple_row'])){
                        $onesheet->addRow(array($group_rows['simple_row']));
                    }
                    elseif (isset($group_rows['general_row'])){
                        $onesheet->addRow($group_rows['general_row']);
                    }

                    else{
                        $header = [];
                        $first_row = $group_rows[0];
                        foreach ($first_row as $key => $value){
                            $heading_text = $string_instance->readable_text($key);
                            $header[] = $heading_text;
                        }
                        $onesheet->addRow($header,$boldHeader);

                        foreach ($group_rows as $column){
                            $column_data = [];
                            foreach ($column as $key => $value){
                                $column_data[] = $value;
                            }
                            $onesheet->addRow($column_data);
                        }
                    }

                }

                $file_name = $type."_excel_data.xlsx";
                $folder = "uploads/export";
                $folder_maker = $file_instance->folder_maker($folder);
                if ($folder_maker){
                    $full_file_path = $folder_maker.$file_name;
                    $debugger->add_property('file_name',$file_name);
                    $debugger->add_property('file_path',$full_file_path);
                    $onesheet->writeToFile(PROJECT_ROOT.$full_file_path);
                }
                else{
                    $debugger->add_error("Folder maker failed");
                }
            }
            else{
                $debugger->add_message("Empty data for excel file");
            }

        }catch (Exception $exception){
            $debugger->add_error("Excel file failed ".$exception->getMessage());
        }
        if (!$debugger->issue_found()){
            $debugger->status = 1;
        }
        return $debugger;

    }
}