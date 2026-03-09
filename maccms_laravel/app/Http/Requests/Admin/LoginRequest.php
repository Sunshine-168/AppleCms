<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class LoginRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'admin_name' => ['required'],
            'admin_pwd' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'admin_name.required' => $this->transMessage('validate/require_name', '账号必须'),
            'admin_pwd.required' => $this->transMessage('validate/require_pwd', '密码必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'admin_name' => 100,
            'admin_pwd' => 255,
        ];
    }
}
