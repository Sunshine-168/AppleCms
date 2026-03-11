<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class TemplateInfoRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        $rules = [
            'file' => ['required'],
        ];

        if ($this->isMethod('post')) {
            $rules['content'] = ['nullable'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file.required' => $this->transMessage('validate/require_path', '路径必须'),
        ];
    }
}
