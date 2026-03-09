<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class TypeSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'type_name' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'type_name.required' => $this->transMessage('validate/require_name', '名称必须'),
        ];
    }
}
