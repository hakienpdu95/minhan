<?php

namespace Modules\Survey\Enums;

enum FieldType: int
{
    case Text     = 1;
    case Textarea = 2;
    case Number   = 3;
    case Select   = 4;
    case Radio    = 5;
    case Checkbox = 6;
    case Rating   = 7;
    case Date     = 8;
    case Boolean  = 9;

    public function valueKind(): ValueKind
    {
        return match ($this) {
            self::Text                              => ValueKind::String,
            self::Textarea                          => ValueKind::Text,
            self::Number, self::Rating              => ValueKind::Number,
            self::Select, self::Radio, self::Checkbox => ValueKind::Option,
            self::Date                              => ValueKind::Date,
            self::Boolean                           => ValueKind::Bool,
        };
    }

    public function isChoice(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox]);
    }

    public function allowsMultiple(): bool
    {
        return $this === self::Checkbox;
    }
}
