<?php

namespace Modules\BusinessProject\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\BusinessProject\Models\ChangeRequest;

class ChangeRequestSubmittedForApproval
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ChangeRequest $changeRequest) {}
}
