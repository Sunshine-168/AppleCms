<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class UserSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'user_name' => ['required', 'min:6'],
            'group_id' => ['required'],
            'user_pwd' => $this->route('id') ? ['nullable'] : ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'user_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'user_name.min' => $this->transMessage('validate/require_name_min', '名称最少不能低于6个字符'),
            'group_id.required' => '用户组必须',
            'user_pwd.required' => $this->transMessage('validate/require_pass', '密码必须'),
        ];
    }
}
