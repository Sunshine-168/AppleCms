<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\MaccmsFormRequest;

class UserListRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
            'user_name' => ['nullable', 'max:50'],
            'group_id' => ['nullable', 'integer', 'min:1'],
            'orderby' => ['nullable', 'in:id,time,login_time,points'],
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'user_name' => 50,
        ];
    }
}
