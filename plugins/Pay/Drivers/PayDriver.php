<?php

namespace Plugins\Pay\Drivers;

use App\Models\Member\MemberOrder;
use Plugins\Pay\Models\PayChannel;

interface PayDriver
{
    /** @return array{code:int,msg:string,data?:array<string,mixed>} */
    public function create(MemberOrder $order, PayChannel $channel): array;

    /** @param  array<string, mixed>  $payload */
    public function notify(array $payload): string;
}
