
<?php
//The socket functions described here are part of an extension to PHP which must be enabled at compile time by giving the --enable-sockets option to configure.
//Add extension=php_sockets.dll in php.ini and extension=sockets
//user defined rule 4
//super admin rule 14
//normal user 0
    require_once 'header.php';
?>

<?php

    if (isset($_REQUEST['save'])){
        $debugger_instance = new Debugger();
        $validation_instance = new DataValidator();
        $rules = [
            'ip'                  => 'required|ip:ipv4',
            'server'                  => 'required|url:http,https',
        ];

        $validation = $validation_instance->validate($rules);
        if (!$validation->status){
            $debugger_instance->messages = $validation->messages;
            $settings->assistant->print_array($debugger_instance->conclusion());
        }
        else{
            try{
                $settings->type = "software_ip";
                $settings->update_properties($settings->type);
                $settings->value = $_REQUEST['ip'];
                $settings->assign();

                $settings->type = "software_server";
                $settings->update_properties($settings->type);
                $settings->value = $_REQUEST['server'];
                $settings->assign();

                
                $settings->assistant->print_on_browser("Changed has been saved.");
            }catch (Exception $exception){
                $settings->assistant->print_on_browser($exception->getMessage());
            }

        }
        
        $settings->ip = $_REQUEST['ip'];
        $settings->server = $_REQUEST['server'];
    }
    ?>
    <form>
        <table>
            <tr>
                <td>Device IP</td>
                <td>:</td>
                <td>
                    <input type="tel" name="ip" value="<?php echo $settings->ip; ?>">
                </td>
            </tr>

            <tr>
                <td>Server address</td>
                <td>:</td>
                <td>
                    <input type="text" name="server" value="<?php echo $settings->server; ?>">
                </td>
            </tr>
            
            <tr>
                <td colspan="3">
                    <button name="save">Save</button>
                </td>
            </tr>

        </table>
    </form>
<?php require_once 'footer.php'; ?>