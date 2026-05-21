<?php

namespace Modules\Survey\Enums;

enum ResponseStatus: int
{
    case Partial  = 0;
    case Complete = 1;
}
