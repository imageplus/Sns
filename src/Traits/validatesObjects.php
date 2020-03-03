<?php


namespace Imageplus\Sns\src\Traits;


use Illuminate\Support\Facades\Validator;

trait validatesObjects
{
    private $errors = [];

    /**
     * @param $rules
     * @param $values
     * @param null $name
     * @return bool
     */
    public function validate($rules, $values): bool{
        $validator = $this->validateArray($rules, $values);

        if($validator->fails()){
            $this->errors = $validator->errors();
            return false;
        }

        return true;
    }

    public function getErrors(){
        return $this->errors;
    }

    protected function validateArray($rules, $values){
        $validator = Validator::make(
            $values,
            $rules
        );

        return $validator;
    }
}
