<?php

namespace App\Http\Requests;

class UserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "firstname" => "required",
            "lastname" => "required",
        ];

        if ($this->getMethod() == "POST") {
            $rules += [
                "email" => "required|email|unique:users",
                "password" => "required|min:8",
            ];
        }

        if ($this->getMethod() == "PATCH") {
            $rules += [
                "email" => "required|email|unique:users,email," . $this->route("id")
            ];
        }
        return $rules;
    }
}
