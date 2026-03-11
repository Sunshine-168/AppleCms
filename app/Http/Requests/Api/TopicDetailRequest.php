<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class TopicDetailRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
