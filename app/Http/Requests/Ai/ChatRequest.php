<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'thread_id' => ['nullable', 'string', 'exists:chat_threads,id'],
            'prompt' => ['required', 'string'],
            'context_module' => ['nullable', 'string'],
        ];
    }
}
