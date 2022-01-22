<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        return [
            'email'     => 'required|email|exists:users',
            'password'  => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'email.required'        => 'Email is required',
            'email.email'           => 'Enter a valid email',
            'email.exists'          => 'This email doesn\'t exists',
            'password.required'     => 'Password is required',
            'password.string'       => 'Password must be valid',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        return response()->validation($errors);
    }
}
