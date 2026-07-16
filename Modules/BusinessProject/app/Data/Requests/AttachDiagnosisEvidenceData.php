<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

class AttachDiagnosisEvidenceData extends Data
{
    public function __construct(
        public readonly int $evidence_deliverable_id,
        public readonly string $evidence_type,
        public readonly ?string $note,
    ) {}

    public static function rules(): array
    {
        return [
            'evidence_deliverable_id' => ['required', 'integer', 'exists:deliverables,id'],
            'evidence_type' => ['required', 'string', Rule::in(['interview', 'observation', 'document_review', 'data_review', 'task', 'metric'])],
            'note' => ['nullable', 'string'],
        ];
    }
}
