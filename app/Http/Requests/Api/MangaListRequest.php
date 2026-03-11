<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class MangaListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:0'],
            'type_id' => ['nullable', 'integer', 'min:0'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'manga_letter' => ['nullable', 'max:1'],
            'manga_area' => ['nullable', 'max:20'],
            'manga_year' => ['nullable', 'max:10'],
            'manga_tag' => ['nullable', 'max:100'],
            'manga_name' => ['nullable', 'max:100'],
            'orderby' => ['nullable', 'in:id,time,hits,hits_day,hits_week,hits_month,score'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'manga_letter' => 1,
            'manga_area' => 20,
            'manga_year' => 10,
            'manga_tag' => 100,
            'manga_name' => 100,
        ];
    }
}
