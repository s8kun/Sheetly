<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

                return [

                    'name' => 'sometimes|string|max:255',

                    'email' => 'sometimes|email|max:255|unique:users,email,' . $userId,

                    'role' => 'sometimes|in:student,admin',

                    'password' => 'sometimes|string|min:8|confirmed',

                ];
    }
}
