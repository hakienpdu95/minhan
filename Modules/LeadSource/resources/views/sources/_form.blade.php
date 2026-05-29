<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-3">Thông tin nguồn cơ hội</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Code (only on create) --}}
            @unless(isset($source))
            <div class="form-control md:col-span-2">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Mã nguồn <span class="text-error">*</span></span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       pattern="[a-z0-9_]+" title="Chỉ dùng chữ thường, số và dấu gạch dưới"
                       class="input input-bordered input-sm @error('code') input-error @enderror"
                       placeholder="VD: social_media">
                <p class="text-xs text-base-content/40 mt-1">Chỉ dùng chữ thường, số và _. Không thể thay đổi sau khi tạo.</p>
                @error('code')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>
            @endunless

            {{-- Label --}}
            <div class="form-control md:col-span-2">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Tên hiển thị <span class="text-error">*</span></span>
                </label>
                <input type="text" name="label" value="{{ old('label', $source->label ?? '') }}" required
                       maxlength="64"
                       class="input input-bordered input-sm @error('label') input-error @enderror"
                       placeholder="VD: Mạng xã hội">
                @error('label')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Icon --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Icon (Iconify)</span></label>
                <div class="flex gap-2 items-center">
                    <input type="text" name="icon" value="{{ old('icon', $source->icon ?? '') }}"
                           maxlength="64"
                           class="input input-bordered input-sm flex-1 @error('icon') input-error @enderror"
                           placeholder="VD: mdi:web" id="iconInput">
                    <div class="w-10 h-9 flex items-center justify-center rounded border border-base-300 bg-base-200/50" id="iconPreview">
                        <span class="iconify text-xl text-base-content/70" data-icon="{{ old('icon', $source->icon ?? 'mdi:help-circle') }}"></span>
                    </div>
                </div>
                <p class="text-xs text-base-content/40 mt-1">Iconify icon name. Xem tại <a href="https://icon-sets.iconify.design" target="_blank" class="link link-primary">icon-sets.iconify.design</a></p>
                @error('icon')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Color --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Màu</span></label>
                <div class="flex gap-2 items-center">
                    <input type="color" id="colorPicker" value="{{ old('color', $source->color ?? '#6b7280') }}"
                           class="w-12 h-9 rounded border border-base-300 cursor-pointer p-0.5 bg-base-100">
                    <input type="text" name="color" id="colorText" value="{{ old('color', $source->color ?? '#6b7280') }}"
                           maxlength="16"
                           class="input input-bordered input-sm flex-1 font-mono"
                           placeholder="#6b7280">
                </div>
                @error('color')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Sort order --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Thứ tự hiển thị</span></label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $source->sort_order ?? 0) }}"
                       min="0" max="255"
                       class="input input-bordered input-sm @error('sort_order') input-error @enderror">
                @error('sort_order')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('colorPicker');
    const text   = document.getElementById('colorText');
    const iconInput   = document.getElementById('iconInput');
    const iconPreview = document.getElementById('iconPreview');

    picker?.addEventListener('input', () => { text.value = picker.value; });
    text?.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
            picker.value = text.value;
        }
    });

    // Icon preview — Iconify auto-renders data-icon spans
    iconInput?.addEventListener('input', function () {
        const span = iconPreview.querySelector('.iconify');
        if (span) {
            span.setAttribute('data-icon', this.value || 'mdi:help-circle');
            // Trigger Iconify re-render if available
            if (window.Iconify) window.Iconify.scan(iconPreview);
        }
    });
});
</script>
@endpush
