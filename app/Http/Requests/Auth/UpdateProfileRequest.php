<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'password'              => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
        ];
    }

    public function messages()
    {
        return [
            'name.required'                         => 'Name is required',
            'name.string'                           => 'Name must be valid',
            'name.max'                              => 'Name can have max 255 characters only',
            'password.string'                       => 'Password must be valid',
            'password.min'                          => 'Password must have minimum 8 characters',
            'password.confirmed'                    => 'Confirmation password doesn\'t match',
            'password_confirmation.required_if'     => 'Confirmation password is required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        return response()->validation($errors);
    }
}
