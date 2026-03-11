<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class TopicListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:0'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'topic_name' => ['nullable', 'max:100'],
            'topic_tag' => ['nullable', 'max:100'],
            'orderby' => ['nullable', 'in:id,time,hits,hits_day,hits_week,hits_month,sort'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'topic_name' => 100,
            'topic_tag' => 100,
        ];
    }
}
