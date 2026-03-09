<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class ArtListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:0'],
            'type_id' => ['nullable', 'integer', 'min:0'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'art_name' => ['nullable', 'max:100'],
            'orderby' => ['nullable', 'in:id,time,time_add,score,hits,hits_day,hits_week,hits_month,up,down,level'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'art_name' => 100,
        ];
    }
}
