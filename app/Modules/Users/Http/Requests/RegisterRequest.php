<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required|min:3|max:100',
            'email' => 'required|email',
//            'email' => 'required|email|unique:users,email',
            'password' => 'min:6',

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
