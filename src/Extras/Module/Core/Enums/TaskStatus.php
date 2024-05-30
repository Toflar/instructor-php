<?php

namespace Cognesy\Instructor\Extras\Module\Core\Enums;

enum TaskStatus : string
{
    case Created = 'created';
    case Ready = 'ready';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';
}
