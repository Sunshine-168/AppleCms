<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class GbookListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'rid' => ['nullable', 'integer', 'min:1'],
            'uid' => ['nullable', 'integer', 'min:0'],
            'orderby' => ['nullable', 'in:id,time'],
        ];
    }
}
