
<?php
//The socket functions described here are part of an extension to PHP which must be enabled at compile time by giving the --enable-sockets option to configure.
//Add extension=php_sockets.dll in php.ini and extension=sockets
//user defined rule 4
//super admin rule 14
//normal user 0
    require_once 'header.php';
?>

<?php
    echo 'User Sync start ...<br />';

    $user_instance = (new User());

    if ($user_instance->save_data_from_device()){
        (new CommonAssistant())->print_on_browser("Done");
    }

    ?>

<?php require_once 'footer.php'; ?>