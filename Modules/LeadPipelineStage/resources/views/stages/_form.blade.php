<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-3">Thông tin tình trạng</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Code (only on create) --}}
            @unless(isset($stage))
            <div class="form-control md:col-span-2">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Mã tình trạng <span class="text-error">*</span></span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       pattern="[a-z0-9_]+" title="Chỉ dùng chữ thường, số và dấu gạch dưới"
                       class="input input-bordered input-sm @error('code') input-error @enderror"
                       placeholder="VD: qualified">
                <p class="text-xs text-base-content/40 mt-1">Chỉ dùng chữ thường, số và _. Không thể thay đổi sau khi tạo.</p>
                @error('code')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>
            @endunless

            {{-- Label --}}
            <div class="form-control md:col-span-2">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Tên hiển thị <span class="text-error">*</span></span>
                </label>
                <input type="text" name="label" value="{{ old('label', $stage->label ?? '') }}" required
                       maxlength="64"
                       class="input input-bordered input-sm @error('label') input-error @enderror"
                       placeholder="VD: Đủ điều kiện">
                @error('label')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Color --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Màu <span class="text-error">*</span></span>
                </label>
                <div class="flex gap-2 items-center">
                    <input type="color" name="color" value="{{ old('color', $stage->color ?? '#6b7280') }}"
                           class="w-12 h-9 rounded border border-base-300 cursor-pointer p-0.5 bg-base-100"
                           id="colorPicker">
                    <input type="text" id="colorText" value="{{ old('color', $stage->color ?? '#6b7280') }}"
                           maxlength="16"
                           class="input input-bordered input-sm flex-1 font-mono"
                           placeholder="#6b7280">
                </div>
                @error('color')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Sort order --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Thứ tự hiển thị</span></label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $stage->sort_order ?? 0) }}"
                       min="0" max="255"
                       class="input input-bordered input-sm @error('sort_order') input-error @enderror">
                @error('sort_order')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Probability --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Xác suất chốt (%)</span>
                </label>
                <input type="number" name="probability" value="{{ old('probability', $stage->probability ?? 0) }}"
                       min="0" max="100"
                       class="input input-bordered input-sm @error('probability') input-error @enderror"
                       placeholder="0–100">
                <p class="text-xs text-base-content/40 mt-1">Dùng tính weighted pipeline value</p>
                @error('probability')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Terminal stage flags --}}
            <div class="form-control md:col-span-2">
                <label class="label py-0 pb-2"><span class="label-text font-medium">Loại tình trạng kết thúc</span></label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_won" value="1" class="checkbox checkbox-success checkbox-sm"
                               {{ old('is_won', $stage->is_won ?? false) ? 'checked' : '' }}>
                        <span class="text-sm">Thành công (Won)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_lost" value="1" class="checkbox checkbox-error checkbox-sm"
                               {{ old('is_lost', $stage->is_lost ?? false) ? 'checked' : '' }}>
                        <span class="text-sm">Thất bại / Không phù hợp (Lost)</span>
                    </label>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('colorPicker');
    const text   = document.getElementById('colorText');
    // Sync color picker ↔ text input
    picker?.addEventListener('input', () => { text.value = picker.value; });
    text?.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
            picker.value = text.value;
        }
    });
    // The actual `name="color"` is on the text input but we need to make sure
    // the picker drives it. Swap: text input has name, picker syncs to it.
    if (picker && text) {
        // Remove name from text, add to a hidden field driven by picker
        picker.addEventListener('change', () => { text.value = picker.value; });
    }
});
</script>
@endpush
