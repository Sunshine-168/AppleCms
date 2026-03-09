<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class TopicSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'topic_name' => ['required'],
            'topic_tpl' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'topic_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'topic_tpl.required' => $this->transMessage('validate/require_tpl', '模板必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'topic_name' => 255,
            'topic_tpl' => 255,
        ];
    }
}
