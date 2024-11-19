
<?php
    require_once 'controllers/packages/vendor/autoload.php';
    $attendance_instance = new AttendanceLog();
    echo $attendance_instance->save_data_from_device(false,true);

    ?>
