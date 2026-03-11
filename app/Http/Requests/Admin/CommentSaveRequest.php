<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class CommentSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        $rules = [
            'comment_name' => ['required'],
            'comment_content' => ['required'],
        ];

        if (!$this->route('id')) {
            $rules['comment_mid'] = ['required'];
            $rules['comment_rid'] = ['required'];
        }

        return $this->postOnlyRules($rules);
    }

    public function messages(): array
    {
        return [
            'comment_name.required' => $this->transMessage('validate/require_nick', '昵称必须'),
            'comment_content.required' => $this->transMessage('validate/require_content', '内容必须'),
            'comment_mid.required' => $this->transMessage('validate/require_mid', '模型id必须'),
            'comment_rid.required' => $this->transMessage('validate/require_rid', '关联id必须'),
        ];
    }
}
