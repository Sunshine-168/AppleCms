<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class TypeListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'mid' => ['nullable', 'integer', 'min:0'],
            'parent' => ['nullable', 'integer', 'min:0'],
            'ids' => ['nullable', 'regex:/^\d+(,\d+)*$/'],
            'orderby' => ['nullable', 'in:id,sort'],
        ];
    }
}
