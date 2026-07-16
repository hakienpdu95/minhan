<?php

namespace Modules\BusinessProject\Enums;

/**
 * Handbook 4.5 Bước 3 — nhóm vấn đề trước khi tìm nguyên nhân gốc: People → Process → Data →
 * Digital → Management.
 */
enum DiagnosisCategory: string
{
    case People = 'people';
    case Process = 'process';
    case Data = 'data';
    case Digital = 'digital';
    case Management = 'management';

    public function label(): string
    {
        return match ($this) {
            self::People => 'People',
            self::Process => 'Process',
            self::Data => 'Data',
            self::Digital => 'Digital',
            self::Management => 'Management',
        };
    }
}
