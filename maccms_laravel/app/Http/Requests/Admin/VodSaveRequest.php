<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\MaccmsFormRequest;

class VodSaveRequest extends MaccmsFormRequest
{
    public function rules(): array
    {
        return $this->postOnlyRules([
            'vod_name' => ['required'],
            'type_id' => ['required'],
        ]);
    }

    public function messages(): array
    {
        return [
            'vod_name.required' => $this->transMessage('validate/require_name', '名称必须'),
            'type_id.required' => $this->transMessage('validate/require_type', '分类必须'),
        ];
    }

    protected function sanitizeMap(): array
    {
        return [
            'vod_name' => 255,
            'vod_sub' => 255,
            'vod_en' => 255,
            'vod_color' => 6,
            'vod_tag' => 100,
            'vod_class' => 255,
            'vod_pic' => 1024,
            'vod_pic_thumb' => 1024,
            'vod_pic_slide' => 1024,
            'vod_pic_screenshot' => 65535,
            'vod_actor' => 255,
            'vod_director' => 255,
            'vod_writer' => 100,
            'vod_behind' => 100,
            'vod_blurb' => 255,
            'vod_remarks' => 100,
            'vod_pubdate' => 100,
            'vod_serial' => 20,
            'vod_tv' => 30,
            'vod_weekday' => 30,
            'vod_area' => 20,
            'vod_lang' => 10,
            'vod_year' => 10,
            'vod_version' => 30,
            'vod_state' => 30,
            'vod_author' => 60,
            'vod_jumpurl' => 150,
            'vod_tpl' => 30,
            'vod_tpl_play' => 30,
            'vod_tpl_down' => 30,
            'vod_duration' => 10,
            'vod_reurl' => 255,
            'vod_rel_vod' => 255,
            'vod_rel_art' => 255,
            'vod_pwd' => 10,
            'vod_pwd_url' => 255,
            'vod_pwd_play' => 10,
            'vod_pwd_play_url' => 255,
            'vod_pwd_down' => 10,
            'vod_pwd_down_url' => 255,
            'vod_play_from' => 255,
            'vod_play_server' => 255,
            'vod_play_note' => 255,
            'vod_down_from' => 255,
            'vod_down_server' => 255,
            'vod_down_note' => 255,
        ];
    }
}
