<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;

class MyErrorController extends BaseController
{
    public function notFound()
    {
        return $this->pageError(__('page_not_found'));
    }
}
