<?php

namespace App\Http\Requests\Web;

use App\Http\Requests\MaccmsFormRequest;

class CommentSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'comment_content' => ['required', 'string', 'max:255'],
            'comment_mid' => ['required', 'integer'],
            'comment_rid' => ['required', 'integer'],
            'comment_pid' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment_content.required' => $this->transMessage('validate/require_content', '内容必须'),
            'comment_mid.required' => $this->transMessage('validate/require_mid', '模型必须'),
            'comment_rid.required' => $this->transMessage('validate/require_rid', '关联ID必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'comment_content' => 255,
        ];
    }
}
