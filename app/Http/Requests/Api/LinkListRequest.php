<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class LinkListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'type' => ['nullable', 'integer', 'min:0'],
            'orderby' => ['nullable', 'in:id,sort'],
        ];
    }
}
