<?php

namespace App\Http\Requests\Vote;

use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'story_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'story_description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
