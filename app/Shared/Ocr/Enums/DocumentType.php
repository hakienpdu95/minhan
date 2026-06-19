<?php

namespace App\Shared\Ocr\Enums;

enum DocumentType: string
{
    case CCCD_FRONT = 'cccd_front';
    case CCCD_BACK  = 'cccd_back';
    case PASSPORT   = 'passport';
    case GENERIC    = 'generic';
}
