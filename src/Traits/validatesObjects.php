<?php


namespace Imageplus\Sns\Traits;


use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

trait validatesObjects
{
    /**
     * @var MessageBag|null
     */
    private $errors = null;

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

    /**
     * Gets error messages from the class
     * @return MessageBag|null
     */
    public function getErrors(){
        return $this->errors;
    }

    /**
     * Creates the validator required
     * @param $rules
     * @param $values
     * @return ValidatorContract
     */
    protected function validateArray($rules, $values): ValidatorContract
    {
        return Validator::make(
            $values,
            $rules
        );
    }
}
