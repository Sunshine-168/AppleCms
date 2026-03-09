<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class CardSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'num' => ['required', 'integer', 'min:1'],
            'money' => ['required', 'numeric', 'min:0'],
            'point' => ['required', 'integer', 'min:1'],
        ]);
    }

    public function messages(): array
    {
        return [
            'num.required' => __('admin/card/please_input_make_num'),
            'num.integer' => __('admin/card/please_input_make_num'),
            'money.required' => __('admin/card/please_input_money'),
            'point.required' => __('admin/card/please_input_points'),
        ];
    }
}
