<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class ActorSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'actor_name' => ['required'],
            'type_id' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'actor_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'type_id.required' => $this->transMessage('validate/require_type', '分类必须'),
        ];
    }
}
