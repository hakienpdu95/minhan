@if($errors->any())
<div class="alert alert-error mb-4 text-sm">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="form-control mb-3">
    <label class="label py-0 pb-1"><span class="label-text font-medium">Loại Deliverable <span class="text-error">*</span></span></label>
    <select name="type" class="select select-bordered select-sm w-full" required>
        <option value="">— Chọn loại —</option>
        @foreach($types as $t)
        <option value="{{ $t->value }}" {{ old('type', $template->type ?? '') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
        @endforeach
    </select>
</div>

<div class="form-control mb-3">
    <label class="label py-0 pb-1"><span class="label-text font-medium">Tên Template <span class="text-error">*</span></span></label>
    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" class="input input-bordered input-sm w-full" required>
</div>

<div class="form-control mb-3">
    <label class="label py-0 pb-1"><span class="label-text font-medium">Mô tả</span></label>
    <textarea name="description" rows="2" class="textarea textarea-bordered textarea-sm w-full">{{ old('description', $template->description ?? '') }}</textarea>
</div>

<div class="form-control mb-3">
    <label class="label py-0 pb-1">
        <span class="label-text font-medium">Nội dung mẫu (JSON) <span class="text-error">*</span></span>
        <span class="label-text-alt text-xs text-base-content/40">Cùng shape với content của deliverable loại này, VD {"solution": "...", "collaboration_plan": "..."}</span>
    </label>
    <textarea name="content_json" rows="8" class="textarea textarea-bordered textarea-sm w-full font-mono text-xs" required>{{ old('content_json', isset($template) ? json_encode($template->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
</div>

@if(isset($template))
<div class="form-control mb-3">
    <label class="flex items-center gap-2 cursor-pointer select-none">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
        <span class="text-sm">Đang sử dụng (bỏ chọn để ngừng dùng, không xóa)</span>
    </label>
</div>
@endif

<div class="flex items-center gap-2">
    <button type="submit" class="btn btn-primary btn-sm">{{ isset($template) ? 'Cập nhật' : 'Tạo Template' }}</button>
    <a href="{{ route('backend.template-library.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
</div>
