
<?php
    require_once 'controllers/packages/vendor/autoload.php';
    $attendance_instance = new AttendanceLog();

//    set_time_limit(0);
    echo $attendance_instance->sync_on_server(false);

    ?>
