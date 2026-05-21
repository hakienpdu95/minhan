<?php

namespace Modules\Survey\Enums;

enum SurveyStatus: int
{
    case Draft  = 0;
    case Active = 1;
    case Closed = 2;
}
