<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class ArtSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'art_name' => ['required'],
            'type_id' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'art_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'type_id.required' => $this->transMessage('validate/require_type', '分类必须'),
        ];
    }
}
