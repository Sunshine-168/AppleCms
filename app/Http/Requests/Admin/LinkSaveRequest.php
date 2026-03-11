<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class LinkSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'link_name' => ['required'],
            'link_url' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'link_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'link_url.required' => $this->transMessage('validate/require_url', '网址必须'),
        ];
    }
}
