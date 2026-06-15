<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'profile_picture_url' => 'sometimes|image|mimes:jpeg,jpg,png,webp|max:4096',
            'verification_token' => 'required_with:password|string|max:6',
            'password' => 'required_with:verification_token|string|min:8|confirmed',
        ];
    }
}
