<?php
define("PROJECT_ROOT",__DIR__."/");

require_once 'controllers/packages/vendor/autoload.php';

if (!file_exists('database.sql')){
    $init_result = (new DbInit())->execute();
    if (!$init_result->status){

        $execution_print_assistant = new ExecutionPrintAssistant();
        $execution_print_assistant->print_array($init_result->conclusion());

        if (file_exists('database.sql')){
            unlink('database.sql');
        }
        die("Database initiate failed");
    }

}

$path_info = pathinfo($_SERVER['REQUEST_URI']);
$extension = $path_info['extension'] ?? '';
$page_name = "";
if ($extension){
    $page_name = $path_info['filename'];
}
$settings = new Setting();
$settings->update_property();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo (new StringAction())->readable_text($page_name); ?></title>
    <link href="style.css" rel="stylesheet" media="all">
</head>
<body>
<div class="container">
<?php require_once 'menu.php';?>