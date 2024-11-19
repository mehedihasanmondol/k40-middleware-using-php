<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 7/11/2021
 * Time: 5:15 PM
 */

class User
{
    public $model;
    public $assistant;

    use \App\User\Model\UserTrait;

    public $assigned = 0;

    public function __construct($connection = null,$device_instance=null)
    {

        $this->model = new App\User\Model\User();
        $this->assistant = new CommonAssistant($connection);

    }

    public function update_properties($uid){
        $columns = $this->assistant->crud->columns_by_model(new App\User\Model\UserView());
        $sql = "where uid = :uid ";
        $data = array(
            ":uid" => $uid,
        );
        $result = $this->assistant->crud->retriever("users",$columns,$sql,$data);

        foreach ($result as $key => $value){
            $this->$key = $value;
        }
        if ($result){
            $this->assigned = 1;
        }
        else{
            $this->assigned = 0;
        }
        return true;

    }


    public function get_user(){
        return $this->assistant->crud->object_vars_by_model(new App\User\Model\UserView(),$this);
    }

  
    public function save_data_from_device(){

        $status = 0;
        $user_data = (new Device($this->assistant->crud->connection))->instance->getUser();
        if ($user_data){
            try{
                $this->clear_users();
                foreach ($user_data as $key => $user){
                    $this->uid = $user[0];
                    if ($this->uid){
                        $this->name = $user[1];
                        $this->roll = $user[2];
                        $this->password = $user[3];

                        $this->create();
                    }

                }
                $status = 1;
            }catch (Exception $exception){

            }

        }

        return $status;
    }




    public function create(){
        $result = $this->assistant->crud->creator("users",$this->assistant->crud->prepare_insert_data($this->model,$this));
        $this->id = $this->assistant->crud->last_insert_id();
        return $result;
    }

    public function clear_users(){
        return $this->assistant->crud->deleter("users","");
    }
}