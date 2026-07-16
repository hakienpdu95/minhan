<?php

namespace Modules\BusinessProject\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\Lead\Models\Lead;

class BusinessProjectCreatedFromLead
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly BusinessProject $businessProject,
        public readonly Lead $lead,
    ) {}
}
