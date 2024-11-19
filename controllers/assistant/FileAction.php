<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 2:54 PM
 */

class FileAction
{
    function default_image($type,$all_data=false){
        $data = array(
            "profile" => "static/app/assets/img/default/profile.png",
            "item" => "static/app/assets/img/default/item.png",
            "drug" => "static/app/assets/img/default/drug.png",
            "logo" => "static/app/assets/img/default/logo.png",
            "banner" => "static/app/assets/img/default/banner.png",
            "long_banner" => "static/app/assets/img/default/banner-long.jpg",
            "challan" => "static/app/assets/img/default/challan.png",
            "no_image" => "static/app/assets/img/default/no-image.png",
            "question" => "static/app/assets/img/default/question.jpg",
        );
        if ($all_data){
            return $data;
        }
        elseif (isset($data[$type])){
            return $data[$type];
        }

        return 0;
    }
    function folder_maker($directory){
        $result = "";
        $date_root = PROJECT_ROOT.$directory;
        if (!file_exists($date_root)){
            $status = mkdir($date_root,0755,true);
            if (!$status){
                return "";
            }
        }
        return $directory."/";
    }

    function compress_image($source_url, $destination_url=null, $quality=50)
    {
        $image_info = array(
            "stream" => "",
            "extension" => ""
        );
        try{
            $image = null;
            $info = getimagesize($source_url);
            if (!$destination_url){
                ob_start();
            }

            if ($info['mime'] == 'image/jpeg') {
                $image = imagecreatefromjpeg($source_url);
                if ($image){
                    imagewebp($image, $destination_url, $quality);
                    $image_info['extension'] = "webp";
                }
            }
            elseif ($info['mime'] == 'image/png') {
                $image = imagecreatefrompng($source_url);
                if ($image){
                    imagewebp($image, $destination_url, $quality);
                    $image_info['extension'] = "webp";
                }
            }


            if (!$destination_url){
                $image_info['stream'] = ob_get_contents();
                ob_end_clean();
            }


        }catch (Exception $exception){

        }

        return $image_info;
    }

    function image_uploader($input_name,$prefix,$default='',$with_compress=true){
        $debugger_instance = new Debugger();

        $settings_instance = new Setting();
        $cloud_storage_bucket = new CloudStorageBucket();
        /// if expected file missing or empty
        if (!isset($_FILES[$input_name]['name']) or empty($_FILES[$input_name]['name'])){
            $default_image = $this->default_image($default);
            if ($default_image){
                $data = array(
                    "file_path" => $default_image
                );
                if ($settings_instance->external_storage){
                    $data['file_path'] = $cloud_storage_bucket->file_prefix.$data['file_path'];
                }

                $debugger_instance->status = 1;
                $debugger_instance->data = $data;
            }
            return $debugger_instance;
        }

        $directory = "uploads/media/images/".date("Y/m/d");
        // Optionally you can rename the file on upload
        $new_filename = uniqid();
        $total_file_name = $prefix.$new_filename;
        $total_file_name = preg_replace("/\s+/","_",$total_file_name);
        if ($settings_instance->external_storage){
            $data = array(
                'file_path'  => '',
            );

            try{
                if ($with_compress){
                    $compress = $this->compress_image($_FILES[$input_name]['tmp_name']);
                    if ($compress['stream']){
                        $data['file_path'] = $cloud_storage_bucket->upload_raw_stream($compress['stream'],
                            $directory."/".$total_file_name.'.'.$compress['extension']);
                    }
                    else{
                        $data['file_path'] = $cloud_storage_bucket->upload_file($_FILES[$input_name]['tmp_name'],
                            $directory."/".$total_file_name.'.'.(pathinfo($_FILES[$input_name]['name'])['extension']));
                    }
                }
                else{
                    $data['file_path'] = $cloud_storage_bucket->upload_file($_FILES[$input_name]['tmp_name'],
                        $directory."/".$total_file_name.'.'.(pathinfo($_FILES[$input_name]['name'])['extension']));
                }


            }catch (Exception $exception){
                $debugger_instance->add_error($exception->getMessage());
            }

        }
        else{
            $date_root = PROJECT_ROOT.$directory;
            $this->folder_maker($directory);
            $storage = new \Upload\Storage\FileSystem($date_root);
            $file = new \Upload\File($input_name, $storage);


            $file->setName($total_file_name);

            // Validate file upload
            // MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
            $file->addValidations(array(
                // Ensure file is of type "image/png"
                new \Upload\Validation\Mimetype(array('image/png', 'image/gif','image/jpg','image/jpeg')),

                //You can also add multi mimetype validation
                //new \Upload\Validation\Mimetype(array('image/png', 'image/gif'))

                // Ensure file is no larger than 5M (use "B", "K", M", or "G")
                new \Upload\Validation\Size('100M')
            ));

            // Access data about the file that has been uploaded
            $data = array(
                'file_path'  => $directory."/".$file->getNameWithExtension(),
            );
//            $this->compress_image(PROJECT_ROOT.$data['file_path'],PROJECT_ROOT.$data['file_path']);

            // Try to upload file
            try {
                // Success!
                $file->upload();
            } catch (\Exception $e) {
                // Fail!
                $errors = $file->getErrors();
                $debugger_instance->add_error($errors);
            }
        }

        if (!$debugger_instance->issue_found()){
            $debugger_instance->status = 1;
            $debugger_instance->data = $data;
        }
        return $debugger_instance;
    }

    function multiple_image_uploader($input_name,$prefix,$default=''){
        $debugger = new Debugger();
        $list_of_files = array();
        $settings_instance = new Setting();
        $cloud_storage_bucket = new CloudStorageBucket();
        /// if expected file missing or empty
        if (!isset($_FILES[$input_name]['name']) or empty($_FILES[$input_name]['name'])){
            $default_image = $this->default_image($default);
            if ($default_image){
                $data = array(
                    "file_path" => $default_image
                );
                if ($settings_instance->external_storage){
                    $data['file_path'] = $cloud_storage_bucket->file_prefix.$data['file_path'];
                }

                $list_of_files[] = $data;
            }

        }
        else{
            $directory = "uploads/media/images/".date("Y/m/d");
            $date_root = PROJECT_ROOT.$directory;
            if (!$settings_instance->external_storage){
                $this->folder_maker($directory);

            }


            foreach ($_FILES[$input_name]['name'] as $index => $name){
                if ($name){
                    $sl = $index + 1;
                    $before_file = "";
                    if (isset($_REQUEST['default_file'][$index])){
                        $before_file = $_REQUEST['default_file'][$index];
                    }
                    if ($before_file and !$name){
                        $list_of_files[] = array(
                            "file_path" => $before_file,
                            "index" => $index,
                            "before_file" => true,
                        );
                    }
                    else{
                        $tmp_file = $_FILES[$input_name]['tmp_name'][$index];
                        // Optionally you can rename the file on upload
                        $new_filename = uniqid();
                        $name_split = explode(".",$name);
                        if ($name_split){
                            $extension = $name_split[count($name_split)-1];
                            $name = ".".$extension;
                        }

                        $total_file_name = $prefix.$new_filename."_".$name;

                        $total_file_name = preg_replace("/\s+/","_",$total_file_name);
                        $file_path = $directory."/".$total_file_name;

                        if ($settings_instance->external_storage){
                            try{

                                $compress = $this->compress_image($tmp_file);
                                if ($compress['stream']){
                                    $list_of_files[] = array(
                                        "file_path" => $cloud_storage_bucket->upload_raw_stream($compress['stream'],$file_path.'.'.$compress['extension']),
                                        "index" => $index,
                                    );

                                }
                                else{
                                    $list_of_files[] = array(
                                        "file_path" => $cloud_storage_bucket->upload_file($tmp_file,$file_path),
                                        "index" => $index,
                                    );
                                }


                            }catch (Exception $exception){
                                $debugger->add_error($exception->getMessage());
                            }



                        }
                        else{
                            $file_move = move_uploaded_file($tmp_file,$date_root."/".$total_file_name);
                            if ($file_move){
                                $list_of_files[] = array(
                                    "file_path" => $file_path,
                                    "index" => $index,
                                );
                                $this->compress_image(PROJECT_ROOT.$file_path,PROJECT_ROOT.$file_path);
                            }
                            else{
                                $debugger->add_error( $sl. " file upload failed");
                            }
                        }

                    }
                }
            }

        }
        $debugger->data = $list_of_files;
        if (!$debugger->issue_found()){
            $debugger->status = 1;
        }

        return $debugger;
    }

    function file_copier($file_name,$prefix=''){
        if (!$file_name){
            throw new Exception('file name empty');
        }

        $directory = "media/images/".date("Y/m/d");
        $date_root = PROJECT_ROOT.$directory;
        if (!file_exists($date_root)){
            $status = mkdir($date_root,0777,true);
            if (!$status){
                throw new Exception("Folder not created");
            }
        }

        $explode_path_name = explode("/",$file_name);
        $just_file_name = $explode_path_name[count($explode_path_name) - 1] ?? '';
        $explode_name = explode(".",$just_file_name);

        $name_array = array();
        $extension = $explode_name[count($explode_name) - 1];
//        for($i = 0 ; $i <= count($explode_name) - 2 ; $i++){
//            $name_array[] = $explode_name[$i];
//        }
        $name_array[] = uniqid();
        $name_array[] = ".";
        $name_array[] = $extension;
        $new_name = join("",$name_array);
        $destination = $directory."/".$new_name;
        if (!copy(PROJECT_ROOT.$file_name,PROJECT_ROOT.$destination)){
            throw new Exception('Copy failed');
        }
        return $destination;
    }
    function file_deleter($file_name){

        $result = true;
        $default_images = $this->default_image("",true);

        $settings_instance = new Setting();
        $cloud_storage_bucket = new CloudStorageBucket();
        if ($settings_instance->external_storage){
            foreach ($default_images as $image){
                $image_name = $cloud_storage_bucket->file_prefix.$image;
                if($file_name == $image_name){
                    return true;
                }
            }

            $file_explode = explode($cloud_storage_bucket->file_prefix,$file_name);
            $bucket_file_name = $file_explode[1] ?? '';
            if ($bucket_file_name){
                $result = $cloud_storage_bucket->delete_file($bucket_file_name);
            }

        }
        else{
            foreach ($default_images as $image){
                $image_name = PROJECT_ROOT.$image;
                if($file_name == $image_name){
                    return true;
                }
            }
            if (file_exists($file_name)){
                $status = unlink($file_name);
                if (!$status){
                    $result = false;
                }
            }
            else{
                $result = true;
            }
        }

        return $result;
    }

    function tmp_directory(){
        return ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();

    }

}