<?php

namespace Modules\Recruitment\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Recruitment\Actions\Backend\StoreCandidateAttachmentAction;
use Modules\Recruitment\Enums\AttachmentFileType;
use Modules\Recruitment\Models\RcCandidate;
use Modules\Recruitment\Models\RcCandidateAttachment;

class CandidateAttachmentController extends Controller
{
    public function store(Request $request, RcCandidate $candidate, StoreCandidateAttachmentAction $action): JsonResponse
    {
        $this->authorize('update', $candidate);

        $validated = $request->validate([
            'file'           => ['required', 'file', 'max:10240'], // 10 MB
            'file_type'      => ['required', 'string', 'in:' . implode(',', array_column(AttachmentFileType::cases(), 'value'))],
            'application_id' => ['nullable', 'integer', 'exists:rc_applications,id'],
        ]);

        $attachment = $action->handle($candidate, $request->file('file'), $validated);
        $attachment->load('uploadedBy');

        return response()->json([
            'message'    => 'Đã tải lên file thành công',
            'attachment' => [
                'id'          => $attachment->id,
                'file_name'   => $attachment->file_name,
                'file_url'    => $attachment->file_url,
                'file_type'   => $attachment->file_type?->value,
                'file_label'  => $attachment->file_type?->label(),
                'file_size'   => $attachment->fileSizeFormatted(),
                'uploaded_by' => $attachment->uploadedBy?->name,
                'uploaded_at' => $attachment->uploaded_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function destroy(RcCandidate $candidate, RcCandidateAttachment $attachment): JsonResponse
    {
        $this->authorize('update', $candidate);

        if ($attachment->candidate_id !== $candidate->id) {
            abort(404);
        }

        // Xóa file khỏi storage
        if ($attachment->storage_provider === 'local' && !empty($attachment->storage_key)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->storage_key);
        }

        $attachment->delete();

        return response()->json(['message' => 'Đã xóa file']);
    }
}
