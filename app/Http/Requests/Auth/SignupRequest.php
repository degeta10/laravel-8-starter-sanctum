<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required'         => 'Name is required',
            'name.string'           => 'Name must be valid',
            'name.max'              => 'Name can have max 255 characters only',
            'email.required'        => 'Email is required',
            'email.unique'          => 'Email already registered',
            'password.string'       => 'Password must be valid',
            'password.min'          => 'Password must have minimum 8 characters',
            'password.confirmed'    => 'Confirmation password doesn\'t match',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        return response()->validation($errors);
    }
}
