<?php

namespace Modules\BusinessProject\Enums;

enum ProjectMemberRole: string
{
    case Sponsor = 'sponsor';
    case Owner = 'owner';
    case LeadConsultant = 'lead_consultant';
    case Consultant = 'consultant';
    case Ba = 'ba';
    case Pm = 'pm';
    case CustomerSuccess = 'customer_success';

    public function label(): string
    {
        return match ($this) {
            self::Sponsor => 'Executive Sponsor',
            self::Owner => 'Project Owner',
            self::LeadConsultant => 'Lead Consultant',
            self::Consultant => 'Consultant',
            self::Ba => 'Business Analyst',
            self::Pm => 'Project Manager',
            self::CustomerSuccess => 'Customer Success',
        };
    }
}
