<?php
class MysqlTransaction {

    public $connection;
    public $status = 0;
    public $error = "";
    public $transaction_end_command = false;
    function __construct($call_back,$on_error,$connection=null)
    {
        try {
            if ($connection){
                $this->connection = $connection;
            }
            else{
                $connection_obj = new Connection();
                $this->connection = $connection_obj->connection;
            }

            if (!$this->connection->inTransaction()){
                $this->connection->beginTransaction();
                $this->transaction_end_command = true;
            }

            $call_back($this);
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
            $on_error($this);
        }
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        // যদি সব ডাটা ঠিক মত এন্টী হয় তাহলে commit অথবা rollback করতে হবে।
        if ($this->transaction_end_command){
            if ($this->status){
                $this->connection->commit();
            }
            else{
                $this->connection->rollBack();
            }
        }

    }
}

?>