<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class TimmingInfoRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'name' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => $this->transMessage('validate/require_name', '任务名称必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'name' => 100,
        ];
    }
}
