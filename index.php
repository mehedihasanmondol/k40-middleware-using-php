
<?php
//error_reporting(0);
//The socket functions described here are part of an extension to PHP which must be enabled at compile time by giving the --enable-sockets option to configure.
//Add extension=php_sockets.dll in php.ini and extension=sockets
//user defined rule 4
//super admin rule 14
//normal user 0


    require_once 'header.php';
?>

<?php
    $attendance_instance = new AttendanceLog($settings->assistant->crud->connection);
    if (!$attendance_instance->device_sn){
        echo "<h1>Device connection failed! Please fix your configuration and try again.
                <a href=''>Retry</a>
                </h1>";
        die();
    }
    else{
        echo "<h1>Device connected to ".(new Device($settings->assistant->crud->connection))->instance->getDeviceName()."</h1>";
    }
    // মেশিন কানেকশননে যদি কোন সমস্যা থাকে তাহলে যেন লোড বন্ধ হয়ে যায়। তাই set_limit পরে দেওয়া হয়েছে।
    set_time_limit(0);
    $attendance_instance->save_data_from_device(true,true);
//
    $attendance_instance->sync_on_server(true);

    ?>

<div id="process">
    <span>Process panel &darr;</span>
    <hr>
    <h2 id="process_heading" class="display-inline-block"></h2>
    <div id="process_response" class="display-inline-block"></div>
</div>

<?php

    require_once 'footer.php';

    ?>

<script src="script.js"></script>
<script src="index.js"></script>
