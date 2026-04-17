<?php

namespace App\Http\Requests\Flag;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        return [
            'reason' => 'nullable|string|max:255',
            'theme_id' => 'required_without_all:reported_user_id,review_id|prohibits:reported_user_id,review_id',
            'reported_user_id' => 'required_without_all:theme_id,review_id|prohibits:theme_id,review_id',
            'review_id' => 'required_without_all:theme_id,reported_user_id|prohibits:theme_id,reported_user_id',
        ];
    }
}
