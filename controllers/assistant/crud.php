<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 3:07 PM
 */

class Crud
{
    public $connection;
    function __construct($connection=null)
    {
        if (!$connection) {
            $connection_obj = new Connection();
            $this->connection = $connection_obj->connection;
        }
        else{
            $this->connection = $connection;
        }
    }

    function retriever($table_name,$columns,$sql,$data=array(),$multiple_data=false){
        $find_data = array();
        try{

            $query = $this->connection->prepare("select $columns from $table_name $sql");
            $query->execute($data);

            if ($multiple_data){
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                $find_data = $result;
            }
            else{
                $result = $query->fetch(PDO::FETCH_ASSOC);
                if (isset($result['active'])){
                    $result['active'] = intval($result['active']);
                }
                if ($result){
                    $find_data = $result;
                }
            }
        }catch (Exception $exception){
            throw new Exception($table_name." (retrieve) ".$exception->getMessage()."<br> $sql ");


        }

        return $find_data;
    }
    function data_counter($table_name,$sql,$data=array()){
        $columns = "count(".$table_name.".id)";
        $query = $this->connection->prepare("select $columns from $table_name $sql");
        $query->execute($data);
        $count = $query->fetchColumn();
        return $count;
    }

    function modifier($table_name,$sql,$data=array(),$multiple=false){
        /*
            new format of $data
            array(
               updates => array(
                        "name" => "value"
                    )
               ),
                condition_data => array(

                )
        */

        $param_sql = $sql;

        try{
            if ($multiple){
                $total_data = count($data);
                $total_response = 0;
                foreach ($data as $item){
                    /// if table will be dynamic fom item
                    if (isset($item['table_name'])){
                        $table_name = $item['table_name'];
                        unset($item['table_name']);
                        if (isset($item['updates'])){
                            $new_item = array();
                            $set_columns = array();
                            foreach ($item['updates'] as $key => $value){
                                $new_item[":".$key] = $value;
                                $set_columns[] = $key."=:".$key;
                            }
                            $sql = join(", ",$set_columns)." ";
                            $sql .= $param_sql;
                            $item = array_merge($new_item,$item['condition_data']);

                        }
                        $query = $this->connection->prepare("update $table_name set $sql");
                    }

                    $send = $query->execute($item);
                    if (!$send){
                        $error_info = "Error in $table_name when modify";
                        throw new Exception($error_info);
                    }
                    else{
                        $total_response++;
                    }
                }

                if ($total_data == $total_response){
                    return true;
                }

            }
            else{
                if (isset($data['updates'])){
                    $new_data = array();
                    $set_columns = array();
                    foreach ($data['updates'] as $key => $value){
                        $new_data[":".$key] = $value;
                        $set_columns[] = $key."=:".$key;
                    }
                    $sql = join(", ",$set_columns)." ";
                    $sql .= $param_sql;
                    $data = array_merge($new_data,$data['condition_data']);

                }
                $query = $this->connection->prepare("update $table_name set $sql");
                $action = $query->execute($data);
                if ($action){
                    return true;
                }
                else{
                    $error_info = "Error in $table_name when modify";
                    throw new Exception($error_info);
                }
            }

        }catch (Exception $exception){
            throw new Exception($table_name." (modify) ".$exception->getMessage());
        }


        return true;

    }

    function deleter($table_name,$sql,$data=array(),$multiple=false){

        try{
            $query = $this->connection->prepare("delete from $table_name $sql");
            if ($multiple){
                $total_data = count($data);
                $total_response = 0;
                foreach ($data as $item){
                    $send = $query->execute($item);
                    if (!$send){
                        $error_info = "Error in $table_name when delete";
                        throw new Exception($error_info);
                    }
                    else{
                        $total_response++;
                    }
                }
                if ($total_data == $total_response){
                    return true;
                }

            }
            else{

                $action = $query->execute($data);
                if ($action){
                    return true;
                }
                else{
                    $error_info = "Error in $table_name when delete";
                    throw new Exception($error_info);
                }
            }

        }catch (Exception $exception){
            throw new Exception($table_name." (delete) ".$exception->getMessage());
        }



    }

    function creator($table_name,$data=array(),$multiple = false,$get_inserted_ids = false){
        $inserted_ids = array();

        try{
            if ($multiple){
                $total_data = count($data);
                $total_response = 0;
                foreach ($data as $item){

                    $new_data = array();
                    $columns = array();
                    $values = array();

                    foreach ($item as $key => $value){
                        $new_data[":".$key] = $value;
                        $columns[] = $key;
                        $values[] = ":".$key;
                    }

                    $columns = join(",",$columns);
                    $values = join(",",$values);
                    $item = $new_data;

                    $query = $this->connection->prepare("insert into $table_name ($columns) values ($values)");

                    $send = $query->execute($item);
                    if (!$send){
                        $error_info = "Error in $table_name when create";
                        throw new Exception($error_info);
                    }
                    else{
                        if ($get_inserted_ids){
                            $inserted_ids[] = $this->connection->lastInsertId();

                        }
                        $total_response++;
                    }
                }

                if ($get_inserted_ids){
                    return $inserted_ids;
                }
                if ($total_data == $total_response){
                    return true;
                }


            }
            else{
                $columns = array();
                $values = array();
                $new_data = array();
                foreach ($data as $key => $value){
                    $new_data[":".$key] = $value;
                    $columns[] = $key;
                    $values[] = ":".$key;
                }
                $columns = join(",",$columns);
                $values = join(",",$values);
                $data = $new_data;

                $query = $this->connection->prepare("insert into $table_name ($columns) values ($values)");

                $action = $query->execute($data);
                if ($action){
                    return true;
                }
                else{

                    $error_info = "Error in $table_name when create";
                    throw new Exception($error_info);
                }
            }

        }catch (Exception $exception){
            throw new Exception($table_name." (create) ".$exception->getMessage());
        }


        return true;

    }
    function sql_in_maker($list){
        $list = array_unique($list);
        if (!$list){
            $list = array(' ');
        }
        $ids_for_join = array();
        foreach ($list as $item){
            $ids_for_join[] = "'".$item."'";
        }
        $join_ids = join(",",$ids_for_join);
        return $join_ids;
    }
    function columns_by_model($model_object){
        $vars = get_object_vars($model_object);

        $columns = array_filter(
            array_values($vars),
            function ($value){
                return $value;
            }
        );

        return join(", ",$columns);
    }

    public function prepare_insert_data($model,$object,$exclude_fields = array()){
        $data = get_object_vars($model);
        unset($data['id']);

        foreach ($exclude_fields as $field){
            unset($data[$field]);
        }
        foreach ($data as $key => $value){
            $data[$key] = $object->$key;
        }
        return $data;
    }

    public function object_vars_by_model($model,$object){
        $model_vars = get_object_vars($model);
        $result = array();
        foreach ($model_vars as $key => $value){
            if ($value){
                $result[$key] = $object->$key;
            }
        }
        return $result;
    }
    public function last_insert_id (){
        return $this->connection->lastInsertId();
    }


}