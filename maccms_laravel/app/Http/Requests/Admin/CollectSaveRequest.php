<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class CollectSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'collect_name' => ['required'],
            'collect_url' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'collect_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'collect_url.required' => $this->transMessage('validate/require_url', '网址必须'),
        ];
    }
}
