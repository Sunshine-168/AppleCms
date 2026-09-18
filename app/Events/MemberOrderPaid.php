<?php

namespace App\Events;

use App\Models\Member\MemberOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once when a recharge order becomes paid and fulfillment runs.
 */
class MemberOrderPaid
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MemberOrder $order,
        public readonly bool $firstTime,
    ) {}
}
