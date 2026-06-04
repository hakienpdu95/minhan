<?php

namespace Modules\Sop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Sop\Actions\Backend\DestroySopStepAttachmentAction;
use Modules\Sop\Actions\Backend\StoreSopStepAttachmentAction;
use Modules\Sop\Models\SopStep;
use Modules\Sop\Models\SopStepAttachment;

class SopStepAttachmentController extends Controller
{
    public function index(SopStep $step): JsonResponse
    {
        $this->authorize('view', $step->sop);

        $attachments = SopStepAttachment::where('step_id', $step->id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($a) => [
                'uuid'            => $a->uuid,
                'file_name'       => $a->file_name,
                'file_url'        => $a->file_url,
                'file_type'       => $a->file_type,
                'file_size_kb'    => $a->file_size_kb,
                'file_size_label' => $a->file_size_label,
                'alt_text'        => $a->alt_text,
                'sort_order'      => $a->sort_order,
                'uploaded_at'     => $a->uploaded_at?->format('d/m/Y H:i'),
            ]);

        return response()->json($attachments);
    }

    public function store(Request $request, SopStep $step): JsonResponse
    {
        $this->authorize('update', $step->sop);

        $maxKb = config('sop.attachments.max_size_kb', 20480);
        $mimes = implode(',', config('sop.attachments.allowed_extensions', ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','gif','txt','zip']));

        $request->validate([
            'file'     => "required|file|max:{$maxKb}|mimes:{$mimes}",
        ]);

        $attachment = app(StoreSopStepAttachmentAction::class)->handle($step, $request->file('file'));

        return response()->json([
            'uuid'            => $attachment->uuid,
            'file_name'       => $attachment->file_name,
            'file_url'        => $attachment->file_url,
            'file_type'       => $attachment->file_type,
            'file_size_kb'    => $attachment->file_size_kb,
            'file_size_label' => $attachment->file_size_label,
            'alt_text'        => $attachment->alt_text,
            'sort_order'      => $attachment->sort_order,
            'uploaded_at'     => $attachment->uploaded_at?->format('d/m/Y H:i'),
        ], 201);
    }

    public function destroy(SopStep $step, SopStepAttachment $attachment): JsonResponse
    {
        $this->authorize('update', $step->sop);

        abort_if($attachment->step_id !== $step->id, 404);

        app(DestroySopStepAttachmentAction::class)->handle($attachment);

        return response()->json(['message' => 'OK']);
    }
}
