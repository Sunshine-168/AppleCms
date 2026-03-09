<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class VodDetailRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1', 'required_without:vod_id'],
            'vod_id' => ['nullable', 'integer', 'min:1', 'required_without:id'],
        ];
    }
}
