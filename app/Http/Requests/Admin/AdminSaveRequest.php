<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class AdminSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'admin_name' => ['required'],
            'admin_pwd' => $this->route('id') ? ['nullable'] : ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'admin_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'admin_pwd.required' => $this->transMessage('validate/require_pass', '密码必须'),
        ];
    }
}
