<?php
    use Rakit\Validation\Validator;
    class DataValidator{
        function validate($rules = [],$messages = [], $methods = false){

            $validate_object = new stdClass();
            $validate_object->status = true;
            $validate_object->messages = [];
            $data_methods = $_REQUEST + $_FILES;
            if ($methods){
                $data_methods = $methods;
            }
            $validator = new Validator($messages);
            try{
                $validator->addValidator('positive_number', new CustomizeRules\PositiveNumber());
                $validator->addValidator('greater_than', new CustomizeRules\GreaterThan());
                $validator->addValidator('less_than', new CustomizeRules\LessThan());
                $validator->setValidator('integer', new CustomizeRules\Interger());
            }catch (Exception $exception){
                $validate_object->status = false;
                $validate_object->messages = [$exception->getMessage()];
            }

            $validation = $validator->validate($data_methods, $rules);

            if ($validation->fails()) {
                // handling errors
                $errors = $validation->errors();
                $error_list = $errors->all();
                $validate_object->status = false;
                $validate_object->messages = $error_list;
            }
            return $validate_object;
        }
    }
