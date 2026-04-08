<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $theme = $this->route('theme');

        return $this->user()->can('update', $theme);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'layout_config' => 'sometimes|required|array',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:8192',
            'categories' => 'sometimes|array',
        ];
    }
}
