<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class ActorListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'actor_name' => ['nullable', 'max:50'],
            'orderby' => ['nullable', 'in:id,time,hits,hits_month,hits_week,hits_day'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'actor_name' => 50,
        ];
    }
}
