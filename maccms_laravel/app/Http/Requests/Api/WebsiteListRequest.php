<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class WebsiteListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:0'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'website_name' => ['nullable', 'max:50'],
            'website_letter' => ['nullable', 'max:1'],
            'orderby' => ['nullable', 'in:id,time,hits,hits_day,hits_week,hits_month,sort'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'website_name' => 50,
            'website_letter' => 1,
        ];
    }
}
