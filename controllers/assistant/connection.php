<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/6/2021
 * Time: 3:56 PM
 */

class Connection extends db
{

    public $connection;

    function __construct($connection=null)
    {
        if (!$connection){
            try {
                $pdo = new PDO($this->db_engine.":$this->db_host", $this->db_name, $this->db_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection = $pdo;

            } catch (PDOException $e) {
                die("Database connection failed");
            }
        }


    }

}