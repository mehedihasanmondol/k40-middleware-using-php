<?php
//The socket functions described here are part of an extension to PHP which must be enabled at compile time by giving the --enable-sockets option to configure.
//Add extension=php_sockets.dll in php.ini and extension=sockets
//user defined rule 4
//super admin rule 14
//normal user 0
    require_once 'header.php';
?>

<?php
    $assistant = new CommonAssistant();
//    $assistant->print_on_browser("Export file preparing ...");
    $attendance_export = (new AttendanceExport($assistant->crud->connection))->export();
    if ($attendance_export->status){
//        $assistant->print_on_browser("Wait for download");
//        $assistant->print_on_browser($attendance_export->file_path);

//        $file_url = $attendance_export->file_path;
//        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//        header('Content-Disposition: attachment; filename="' . $file_url . '"');
//        header('Cache-Control: max-age=0');
//        header('Pragma: public');
//
//        readfile($file_url);
//
        echo "<a href='".$attendance_export->file_path."'><button style='margin: 20px;margin-bottom: 200px'>Download Attendance data</button></a>";
    }
    else{
        $assistant->print_on_browser("Failed!");
        $assistant->print_array($attendance_export->conclusion());
    }
    ?>

<?php require_once 'footer.php'; ?>