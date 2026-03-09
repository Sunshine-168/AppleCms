<?php

namespace App\Http\Requests\Web;

use App\Http\Requests\MaccmsFormRequest;

class GbookSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'gbook_content' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'gbook_content.required' => $this->transMessage('validate/require_content', '内容必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'gbook_content' => 255,
        ];
    }
}
