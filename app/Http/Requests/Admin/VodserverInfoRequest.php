<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class VodserverInfoRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'from' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'from.required' => $this->transMessage('validate/require_flag', '服务器标识必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'from' => 100,
        ];
    }
}
