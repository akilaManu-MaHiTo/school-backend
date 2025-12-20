<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [

            'name'              => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'userName'          => ['required', 'string', 'max:255', 'unique:users'],
            'nameWithInitials'  => ['required', 'string', 'max:255'],
            'email'             => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password'          => ['required', 'min:4', 'confirmed', 'max:15'],
            'mobile'            => ['required', 'string', 'max:15',],
            'employeeType'      => ['required', 'string', 'max:255'],
            'employeeNumber'    => ['nullable', 'string', 'max:255', 'unique:users', 'required_if:employeeType,Teacher,Student'],
        ];
    }

    public function messages()
    {
        return [
            'name.max'                    => 'Name must not exceed 255 characters.',
            'name.regex'                  => 'Name must be letters and spaces only.',

            'userName.required'           => 'Username is required.',
            'userName.unique'             => 'Username is already taken.',

            'email.unique'                => 'Email is already taken.',

            'password.required'           => 'Password is required.',
            'password.min'                => 'Password must be at least 4 characters.',

            'mobile.required'             => 'Mobile is required.',

            'employeeType'                => 'Employee Type is Required',
            'employeeNumber.required_if'  => 'Employee number is required when the user is a company employee.',
            'employeeNumber.unique'       => 'Employee number already exists.',

            'nameWithInitials.required'   => 'Name with initials is required.',
        ];
    }
}
