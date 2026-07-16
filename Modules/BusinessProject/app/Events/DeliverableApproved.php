<?php

namespace Modules\BusinessProject\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\BusinessProject\Models\Deliverable;

class DeliverableApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Deliverable $deliverable) {}
}
