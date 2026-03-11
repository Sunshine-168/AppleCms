<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class RoleSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'role_name' => ['required'],
            'role_actor' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'role_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'role_actor.required' => $this->transMessage('validate/require_actor', '演员必须'),
        ];
    }
}
