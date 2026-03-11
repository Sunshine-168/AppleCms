<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class GroupSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'group_name' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'group_name.required' => $this->transMessage('validate/require_name', '名称必须'),
        ];
    }
}
