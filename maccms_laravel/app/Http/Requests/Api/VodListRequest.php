<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class VodListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:0'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'orderby' => ['nullable', 'in:hits,up,pubdate,hits_week,hits_month,hits_day,score,time'],
            'type_id' => ['nullable', 'integer', 'min:0'],
            'vod_letter' => ['nullable', 'max:1'],
            'vod_name' => ['nullable', 'max:50'],
            'vod_tag' => ['nullable', 'max:20'],
            'vod_blurb' => ['nullable', 'max:20'],
            'vod_class' => ['nullable', 'max:10'],
            'vod_area' => ['nullable', 'max:20'],
            'vod_year' => ['nullable', 'max:10'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'vod_letter' => 1,
            'vod_name' => 50,
            'vod_tag' => 20,
            'vod_blurb' => 20,
            'vod_class' => 10,
            'vod_area' => 20,
            'vod_year' => 10,
        ];
    }
}
