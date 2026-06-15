<?php

namespace App\Http\Requests\Theme;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'search' => 'nullable|string',
            'categories' => 'nullable|string',
            'favorites' => 'nullable|boolean',
            'sort' => 'nullable|string|in:recent,downloads,reviews,likes',
        ];
    }
}
