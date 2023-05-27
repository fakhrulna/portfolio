<?php

namespace helpers\Validator;

use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    protected $req;

    protected $errors;

    function __construct()
    {

    }

    /**
     * @param  Request  $request
     * @param  array  $rules
     * @return $this
     */
    public function validate(Request $request, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert($request->getParam($field));
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }


    /**
     * @param $obj
     * @param array $rules
     * @return $this
     */
    public function validateObject($obj, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert($field);
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->errors;
    }

}