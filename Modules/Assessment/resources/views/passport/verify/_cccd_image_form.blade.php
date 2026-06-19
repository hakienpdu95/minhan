{{--
  Partial: Form upload ảnh CCCD (dùng khi đã nhập text trước, hoặc trong full form)
  Props:
    $mode = 'upgrade' | 'inline'  (upgrade: standalone form; inline: dùng trong full form)
--}}
@php $isUpgrade = ($mode ?? 'inline') === 'upgrade'; @endphp

<div x-data="{
    frontFile: false, backFile: false,
    frontPreview: null, backPreview: null,
    loading: false,
    get hasBoth() { return this.frontFile && this.backFile; },
    get hasOne()  { return this.frontFile !== this.backFile; },
    onFile(e, side) {
        const file = e.target.files[0];
        this[side + 'File'] = !!file;
        this[side + 'Preview'] = null;
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => this[side + 'Preview'] = ev.target.result;
        reader.readAsDataURL(file);
    }
}">

@if($isUpgrade)
<form method="POST" action="{{ route('passport.verify.cccd') }}"
      enctype="multipart/form-data" @submit="loading = true">
    @csrf
@endif

    {{-- Image drop zones --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

        {{-- Mặt trước --}}
        <div>
            <p class="text-xs font-medium text-base-content/70 mb-1.5">
                Ảnh mặt trước <span class="text-error">*</span>
                <span class="text-base-content/40 font-normal">· số CCCD, họ tên, ngày sinh</span>
            </p>
            <label for="{{ $isUpgrade ? 'up_front' : 'full_front' }}"
                   class="block cursor-pointer rounded-xl border-2 border-dashed transition overflow-hidden
                          {{ $errors->has('front_image') ? 'border-error' : 'border-base-300 hover:border-primary' }}">
                <div class="relative aspect-[1.586/1] bg-base-200/60 flex items-center justify-center">
                    <img x-show="frontPreview" :src="frontPreview"
                         class="absolute inset-0 w-full h-full object-cover" alt="">
                    <div x-show="!frontPreview" class="text-center px-3 py-4">
                        <svg class="w-8 h-8 mx-auto text-base-content/25 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-xs text-base-content/40">Chọn ảnh mặt trước</p>
                        <p class="text-xs text-base-content/25">JPEG / PNG / WebP · ≤ 5MB</p>
                    </div>
                </div>
            </label>
            <input id="{{ $isUpgrade ? 'up_front' : 'full_front' }}"
                   name="front_image" type="file"
                   accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="hidden" @change="onFile($event, 'front')">
            @error('front_image')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Mặt sau --}}
        <div>
            <p class="text-xs font-medium text-base-content/70 mb-1.5">
                Ảnh mặt sau <span class="text-error">*</span>
                <span class="text-base-content/40 font-normal">· ngày cấp (mặt sau CCCD mới)</span>
            </p>
            <label for="{{ $isUpgrade ? 'up_back' : 'full_back' }}"
                   class="block cursor-pointer rounded-xl border-2 border-dashed transition overflow-hidden
                          {{ $errors->has('back_image') ? 'border-error' : 'border-base-300 hover:border-primary' }}">
                <div class="relative aspect-[1.586/1] bg-base-200/60 flex items-center justify-center">
                    <img x-show="backPreview" :src="backPreview"
                         class="absolute inset-0 w-full h-full object-cover" alt="">
                    <div x-show="!backPreview" class="text-center px-3 py-4">
                        <svg class="w-8 h-8 mx-auto text-base-content/25 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-xs text-base-content/40">Chọn ảnh mặt sau</p>
                        <p class="text-xs text-base-content/25">JPEG / PNG / WebP · ≤ 5MB</p>
                    </div>
                </div>
            </label>
            <input id="{{ $isUpgrade ? 'up_back' : 'full_back' }}"
                   name="back_image" type="file"
                   accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="hidden" @change="onFile($event, 'back')">
            @error('back_image')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

    </div>

    {{-- Warning: chỉ có 1 ảnh --}}
    <div x-show="hasOne" x-cloak
         class="alert alert-warning alert-sm py-2 mb-3 text-xs">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Cần chọn cả 2 ảnh để xác minh qua OCR.</span>
    </div>

    {{-- Tips (chỉ hiện khi có ảnh) --}}
    <div x-show="frontPreview || backPreview" x-cloak
         class="mb-3 p-2.5 bg-info/10 border border-info/20 rounded-lg">
        <p class="text-xs text-info font-medium mb-1">Mẹo chụp ảnh:</p>
        <ul class="text-xs text-base-content/55 list-disc list-inside space-y-0.5">
            <li>Nền tối, ánh sáng đủ — không bị chói hoặc bóng</li>
            <li>Nằm ngang, đủ 4 góc thẻ trong khung</li>
            <li>Tối thiểu 800×500 px, không mờ</li>
        </ul>
    </div>

    @if($isUpgrade)
    {{-- Submit button standalone --}}
    <div class="flex items-center gap-3">
        <button type="submit"
                class="btn btn-primary btn-sm gap-2"
                :disabled="!hasBoth || loading"
                :class="{ 'btn-disabled': !hasBoth || loading }">
            <span x-show="!loading">Xác minh qua OCR — Trust Lv 3</span>
            <span x-show="loading" class="loading loading-spinner loading-xs"></span>
            <span x-show="loading">Đang nhận dạng...</span>
        </button>
        <span x-show="!hasBoth && !hasOne" class="text-xs text-base-content/40">Chọn 2 ảnh để bật xác minh</span>
        <span x-show="loading" class="text-xs text-base-content/40">Mất khoảng 20–60 giây</span>
    </div>
    @endif

@if($isUpgrade)
</form>
@endif

</div>
