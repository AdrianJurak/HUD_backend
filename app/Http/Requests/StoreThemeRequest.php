<?php

namespace App\Http\Requests;

use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;

class StoreThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Theme::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'layout_config' => 'required|array',
            'images' => 'nullable|array|max:5',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:8192',
            'categories' => 'array',
        ];
    }
}
