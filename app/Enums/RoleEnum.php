<?php
namespace App\Enums;

enum RoleEnum: string
{
    case CEO       = 'ceo';
    case SALES     = 'sales';
    case OPS       = 'ops';
    case MARKETING = 'marketing';
    case HR        = 'hr';
    case AI_OP     = 'ai_operator';
    case ADMIN     = 'system_admin';
    case VIEWER    = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::CEO       => 'CEO / Founder',
            self::SALES     => 'Sales Team',
            self::OPS       => 'Operations',
            self::MARKETING => 'Marketing',
            self::HR        => 'HR / Admin Staff',
            self::AI_OP     => 'AI Operator',
            self::ADMIN     => 'System Admin',
            self::VIEWER    => 'Viewer / Partner',
        };
    }

    public function visibleModules(): array
    {
        return match($this) {
            self::CEO    => ['ceo_dashboard','crm','sales_ai','tasks','sop','workflow','prompt','ai_logs','users','reports'],
            self::SALES  => ['crm','sales_ai','tasks','sop','reports'],
            self::OPS    => ['ceo_dashboard','crm','tasks','sop','workflow','ai_logs','reports'],
            self::MARKETING => ['crm','sales_ai','tasks','sop','reports'],
            self::HR     => ['tasks','sop','users','reports'],
            self::AI_OP  => ['ceo_dashboard','crm','tasks','sop','workflow','prompt','ai_logs','reports'],
            self::ADMIN  => ['ceo_dashboard','crm','sales_ai','tasks','sop','workflow','prompt','ai_logs','users','roles','reports','integrations'],
            self::VIEWER => ['ceo_dashboard','tasks','sop','reports'],
        };
    }
}