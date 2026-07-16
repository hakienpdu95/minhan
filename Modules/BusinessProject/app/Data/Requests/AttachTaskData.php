<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class AttachTaskData extends Data
{
    public function __construct(
        public readonly int $task_id,
    ) {}

    public static function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ];
    }
}
