
<?php
//The socket functions described here are part of an extension to PHP which must be enabled at compile time by giving the --enable-sockets option to configure.
//Add extension=php_sockets.dll in php.ini and extension=sockets
//user defined rule 4
//super admin rule 14
//normal user 0
require_once 'controllers/packages/vendor/autoload.php';

require_once 'header.php';
?>
<?php
    $device = new Device();
    $settings = new Setting();
    $settings->update_property();

    ?>
<table>
    <tr>
        <th>Machine name</th>
        <td>:</td>
        <td><?php echo $device->instance->getDeviceName(); ?></td>
    </tr>
    <tr>
        <th>Serial number</th>
        <td>:</td>
        <td><?php echo $device->instance->getSerialNumber(); ?></td>
    </tr>

    <tr>
        <th>Total user</th>
        <td>:</td>
        <td><?php echo count($device->instance->getUser()); ?></td>
    </tr>
    <tr>
        <th>Server</th>
        <td>:</td>
        <td><?php echo $settings->server; ?></td>
    </tr>




</table>

<?php
require_once 'footer.php';
 ?>