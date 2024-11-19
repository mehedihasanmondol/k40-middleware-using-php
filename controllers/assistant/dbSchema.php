<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 3:03 PM
 */

class DbSchema
{
    function get_db_tables_schema($connection=null)
    {
        if (!$connection){
            $connection = $this->connection;
        }
        $prepare = $connection->prepare("show tables");
        $prepare->execute();
        $tables = $prepare->fetchAll(PDO::FETCH_COLUMN);
        $table_schema = array();
        foreach ($tables as $table) {
            $prepare = $connection->prepare("SHOW INDEXES FROM $table");
            $prepare->execute();
            $indexes = $prepare->fetchAll(PDO::FETCH_ASSOC);

            //get table columns
            $prepare = $connection->prepare("SHOW COLUMNS FROM $table");
            $prepare->execute();
            $columns = $prepare->fetchAll(PDO::FETCH_ASSOC);
            $table_schema[] = array(
                "table_name" => $table,
                "columns" => $columns,
                "indexes" => $indexes
            );
        }
        return $table_schema;
    }
    function tables_auto_increment_schema($db_name="",$connection=null)
    {
        if (!$connection){
            $connection = $this->connection;
        }
        $prepare = $connection->prepare("SELECT AUTO_INCREMENT,TABLE_NAME 
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '$db_name'");
        $prepare->execute();
        $schema = $prepare->fetchAll(PDO::FETCH_ASSOC);

        return $schema;
    }
    function column_names_by_table($connection,$table_name){
        $prepare = $connection->prepare("SHOW COLUMNS FROM $table_name");
        $prepare->execute();
        $columns = $prepare->fetchAll(PDO::FETCH_ASSOC);
        $column_names = array();

        foreach ($columns as $info){
            $column_name = $info['Field'];
            $column_names[] = $column_name;

        }
        return $column_names;
    }

    function db_schema_update($materials){
        $return_object = array(
            "status" => 0,
            "errors" => array(),
            "messages" => array()
        );
        // latest db is new changes
        // and old db is updatable
        $requirement = array(
            "latest_db" => array(
                "db_host" => "",
                "db_name" => "",
                "db_user_name" => "",
                "db_password" => "",

            ),
            "old_db" => array(
                "db_host" => "",
                "db_name" => "",
                "db_user_name" => "",
                "db_password" => "",
            ),
        );
        $requirement = array_merge($requirement,$materials);
        $latest_connection = $old_connection = null;
        if ($materials){
            // check material is valid
            if (!isset($materials['latest_db'])){
                $return_object['errors'][] = "Latest database information not set";
            }
            else{
                foreach ($requirement['latest_db'] as $key => $value){
                    if (!$value and $key != "db_password"){
                        $return_object['errors'][] = "Latest database ($key) not set";
                    }
                }
            }
            if (!isset($materials['old_db'])){
                $return_object['errors'][] = "Old database information not set";
            }
            else{
                foreach ($requirement['old_db'] as $key => $value){
                    if (!$value and $key != "db_password"){
                        $return_object['errors'][] = "Old database ($key) not set";
                    }
                }
            }
            if (!$this->error_in_object($return_object)){
                $latest_db_schema = array();
                $old_db_schema = array();
                $old_db_auto_increment_data = array();
                // get latest database schema
                try {
                    $latest_db_host = $requirement['latest_db']['db_host'];
                    $latest_db_name = $requirement['latest_db']['db_name'];
                    $latest_db_user_name = $requirement['latest_db']['db_user_name'];
                    $latest_db_password = $requirement['latest_db']['db_password'];
                    $latest_connection = new PDO("mysql:host=$latest_db_host;dbname=$latest_db_name", $latest_db_user_name, $latest_db_password);
                    $latest_db_schema = $this->get_db_tables_schema($latest_connection);
                } catch (PDOException $e) {
                    $return_object['errors'][] = $e->getMessage();
                }
                // get old database schema
                try {
                    $old_db_host = $requirement['old_db']['db_host'];
                    $old_db_name = $requirement['old_db']['db_name'];
                    $old_db_user_name = $requirement['old_db']['db_user_name'];
                    $old_db_password = $requirement['old_db']['db_password'];
                    $old_connection = new PDO("mysql:host=$old_db_host;dbname=$old_db_name", $old_db_user_name, $old_db_password);
                    $old_db_schema = $this->get_db_tables_schema($old_connection);
//                        $old_db_auto_increment_data = $this->tables_auto_increment_schema($old_db_name,$old_connection);

                } catch (PDOException $e) {
                    $return_object['errors'][] = $e->getMessage();
                }
                if (!$latest_db_schema){
                    $return_object['errors'][] = "Latest database schema empty";
                }
//                    if (!$old_db_schema){
//                        $return_object['errors'][] = "Old database schema empty";
//                    }
                //                print_r($latest_db_schema);
                if ($this->error_in_object($return_object)){
                    return $return_object;
                }
                $update_query = "";

                // compare together and make expected query
                foreach ($latest_db_schema as $latest_table_schema){
                    $table_name = $latest_table_schema['table_name'];
                    $filter_keys = array(
                        "table_name" => $table_name
                    );
                    $old_table_schema = $this->filter_in_array($old_db_schema,$filter_keys,false);
                    // if old table not found as current table name
                    if (!$old_table_schema){

                        $create_params = array();
                        $auto_increment = "";
                        foreach ($latest_table_schema['columns'] as $column){
                            $column_name = $column['Field'];
                            $column_type = $column['Type'];
                            $type_explode = explode("(",rtrim($column_type,")"));
                            $type_name = $type_explode[0];
                            $type_length = "";
                            if (isset($type_explode[1])){
                                $type_length = $type_explode[1];
                            }

                            $column_null = $column['Null'];
                            $column_key = $column['Key'];
                            $column_default = $column['Default'];
                            $column_extra = $column['Extra'];
                            $column_line_sql = "\t $column_name $type_name";
                            if ($type_length){
                                $column_line_sql .= "($type_length) ";
                            }

                            $default_value = " DEFAULT ";
                            if ($column_null == "NO"){
                                $default_value = " NOT ";
                            }
                            $column_line_sql .= $default_value." NULL";
                            if ($column_default){
                                $column_line_sql .= " DEFAULT '$column_default'";
                            }
                            if ($column_extra == "auto_increment"){
                                $auto_increment .= "-- \n";
                                $auto_increment .= "-- Auto increment for table ($table_name) \n";
                                $auto_increment .= "-- \n";
                                $auto_increment .= " ALTER TABLE $table_name \n";
                                $auto_increment .= "\t MODIFY $column_line_sql AUTO_INCREMENT;\n";
                            }
                            $create_params[] = $column_line_sql;


                        }
                        $join_create_params = join(",\n",$create_params);
                        if ($join_create_params){
                            $update_query .= "-- \n";
                            $update_query .= "-- Add new table ($table_name) schema \n";
                            $update_query .= "-- \n";
                            $update_query .= "CREATE TABLE $table_name (\n";
                            $update_query .= $join_create_params;
                            $update_query .= "\n)";
                            $update_query .= ";\n";
                        }

                        // make index query
                        $index_data_obj = array();
                        foreach ($latest_table_schema['indexes'] as $index_data){
                            $index_table_name = $index_data['Table'];
                            $index_key = $index_data['Key_name'];
                            $index_column = $index_data['Column_name'];
                            $index_primary = "";
                            if ($index_key == "PRIMARY"){
                                $index_primary = " PRIMARY";
                                $index_key = "";
                            }
                            else{
                                $index_key = " $index_key";
                            }

                            $index_line = "\t ADD $index_primary KEY $index_key ($index_column)";

                            $index_data_obj[$index_table_name][] = $index_line;

                        }
                        foreach ($index_data_obj as $table_name => $lines){
                            $update_query .= "-- \n";
                            $update_query .= "-- Indexes for  ($table_name) \n";
                            $update_query .= "-- \n ";
                            $update_query .= "ALTER TABLE $table_name \n";
                            $update_query .= join(",\n",$lines);
                        }

                        if ($index_data_obj){
                            $update_query .= ";\n";
                        }
                        // add auto increment line if available
                        if ($auto_increment){
                            $update_query .= $auto_increment;
                        }
                        else{
                            $auto_increment = "";
                        }
                    }
                    // if any change into old and latest table schema
                    elseif($latest_table_schema != $old_table_schema){

                        $update_params = array();
                        $auto_increment = "";
                        foreach ($latest_table_schema['columns'] as $column){
                            // get old column data by this column name
                            $column_name = $column['Field'];
                            $old_column_filter_keys = array(
                                "Field" => $column_name
                            );
                            $old_column = $this->filter_in_array($old_table_schema['columns'],$old_column_filter_keys,false);
                            if ($old_column != $column){

                                $column_type = $column['Type'];
                                $type_explode = explode("(",rtrim($column_type,")"));
                                $type_name = $type_explode[0];
                                $type_length = "";
                                if (isset($type_explode[1])){
                                    $type_length = $type_explode[1];
                                }

                                $column_null = $column['Null'];
                                $column_key = $column['Key'];
                                $column_default = $column['Default'];
                                $column_extra = $column['Extra'];
                                $column_line_sql = " $column_name $type_name";
                                if ($type_length){
                                    $column_line_sql .= "($type_length) ";
                                }

                                $default_value = " DEFAULT ";
                                if ($column_null == "NO"){
                                    $default_value = " NOT ";
                                }
                                $column_line_sql .= $default_value." NULL";
                                if ($column_default){
                                    $column_line_sql .= " DEFAULT '$column_default'";
                                }
                                if ($column_extra == "auto_increment"){
                                    $auto_increment .= "-- \n";
                                    $auto_increment .= "-- Auto increment for table ($table_name) \n";
                                    $auto_increment .= "-- \n";
                                    $auto_increment .= " ALTER TABLE $table_name \n";
                                    $auto_increment .= "\t MODIFY $column_line_sql AUTO_INCREMENT;\n";
                                }
                                // if latest  column not exist. then add this column at old table
                                if (!$old_column){
                                    $update_params[] = "\t ADD ".$column_line_sql;
                                }
                                // if column exist into latest version but something changed. then update this column at old table
                                elseif($old_column != $column){
                                    $update_params[] = "\t MODIFY ".$column_line_sql;
                                }
                            }



                        }
                        $join_update_params = join(",\n",$update_params);
                        if ($join_update_params){
                            $update_query .= "-- \n";
                            $update_query .= "-- Update table ($table_name) schema \n";
                            $update_query .= "-- \n";
                            $update_query .= "ALTER TABLE $table_name \n";
                            $update_query .= $join_update_params;
                            $update_query .= ";\n";
                        }
                        // add auto increment line if available
                        if ($auto_increment){
                            //check auto increment column in old table
                            $increment_filter_keys = array(
                                "Extra" => "auto_increment"
                            );
                            $check_increment = $this->filter_in_array($old_table_schema['columns'],$increment_filter_keys,false);
                            if (!$check_increment){
                                $update_query .= $auto_increment;
                            }

                        }
                        else{
                            $auto_increment = "";
                        }

                        // make index query
                        $index_data_obj = array();
                        foreach ($latest_table_schema['indexes'] as $index_data){

                            $index_table_name = $index_data['Table'];
                            $index_key = $index_data['Key_name'];
                            $index_column = $index_data['Column_name'];

                            // check this index key is exist in old database table
                            $old_index_filter_keys = array(
                                "Column_name" => $index_column
                            );
                            $old_index_data = $this->filter_in_array($old_table_schema['indexes'],$old_index_filter_keys,false);
                            /// remove cardinality eky from both object // and solution will be next time
                            if ($old_index_data){
                                unset($old_index_data['Cardinality']);
                                unset($index_data['Cardinality']);
                            }

                            if ($old_index_data != $index_data){
                                $index_primary = "";
                                if ($index_key == "PRIMARY"){
                                    $index_primary = "PRIMARY";
                                    $index_key = "";
                                }

                                if (!$old_index_data){
                                    $index_line = "\t ADD $index_primary KEY $index_key ($index_column)";
                                    $index_data_obj[$index_table_name][] = $index_line;
                                }
                                elseif( $index_data != $old_index_data){
                                    /// delete old index
                                    if (isset($old_index_data['Key_name'])){
                                        $old_index_key = $old_index_data['Key_name'];
                                        $index_line = "\t DROP INDEX $old_index_key";
                                        $index_data_obj[$index_table_name][] = $index_line;
                                    }
                                    // add new index
                                    $index_line = "\t ADD $index_primary KEY $index_key ($index_column)";
                                    $index_data_obj[$index_table_name][] = $index_line;

                                }
                            }
                        }
                        foreach ($index_data_obj as $table_name => $lines){
                            if ($lines){
                                $update_query .= "--\n";
                                $update_query .= "-- Indexes for  ($table_name) \n";
                                $update_query .= "--\n";
                                $update_query .= "ALTER TABLE $table_name \n";
                                $update_query .= join(",\n",$lines);
                            }

                        }
                        if ($index_data_obj){
                            $update_query .= ";\n";
                        }
                    }

                }
                if ($update_query){
                    try{
                        $old_connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
                        $update_status = $old_connection->exec("START TRANSACTION; ".$update_query." COMMIT;");

                    }catch (Exception $exception){
                        $return_object['errors'][] = $exception->getMessage();
                    }
                }


            }

        }
        else{
            $return_object['errors'][] = "Material empty";
        }
        if (!$this->error_in_object($return_object)){
            $return_object['status'] = 1;
        }
        return $return_object;
    }
}