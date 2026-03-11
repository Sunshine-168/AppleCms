<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class DomainImportRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:txt'],
        ];
    }
}
