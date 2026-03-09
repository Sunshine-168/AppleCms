<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class CjSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'data.name' => ['required'],
            'data.sourcecharset' => ['required'],
            'data.sourcetype' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'data.name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'data.sourcecharset.required' => $this->transMessage('validate/require_sourcecharset', '来源编码必须'),
            'data.sourcetype.required' => $this->transMessage('validate/require_sourcetype', '来源类型必须'),
        ];
    }

}
