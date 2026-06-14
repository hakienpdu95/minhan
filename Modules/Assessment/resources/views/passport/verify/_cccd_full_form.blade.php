{{--
  Partial: Form nhập đầy đủ CCCD (fresh — chưa có record nào)
  - Text fields luôn hiện (cccd_number, name_on_cccd, issue_date)
  - Image upload section optional
  - Alpine.js detect hasBoth → nút submit thay đổi nhãn + hành vi:
      hasBoth = true  → "Xác minh ngay qua OCR (Trust Lv 3)"
      hasBoth = false → "Lưu thông tin, xác minh ảnh sau"
--}}
<form method="POST" action="{{ route('passport.verify.cccd') }}"
      enctype="multipart/form-data"
      x-data="{
          frontFile: false, backFile: false,
          frontPreview: null, backPreview: null,
          loading: false,
          showImageSection: {{ $errors->hasAny(['front_image','back_image']) ? 'true' : 'false' }},
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
      }"
      @submit="loading = true">
@csrf

{{-- ── Thông tin cơ bản (luôn hiện) ─────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

    {{-- Số CCCD --}}
    <div class="sm:col-span-2">
        <label class="label label-text text-sm font-medium pb-1">
            Số CCCD <span class="text-error">*</span>
        </label>
        <input type="text" name="cccd_number" inputmode="numeric" maxlength="12"
               value="{{ old('cccd_number') }}"
               class="input input-bordered w-full font-mono tracking-widest @error('cccd_number') input-error @enderror"
               placeholder="012345678901">
        @error('cccd_number')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Họ tên trên CCCD --}}
    <div class="sm:col-span-2">
        <label class="label label-text text-sm font-medium pb-1">
            Họ tên trên CCCD <span class="text-error">*</span>
            <span class="text-xs text-base-content/40 ml-1 font-normal">
                (khớp ≥ 85% với tên tài khoản: <strong>{{ $user->name }}</strong>)
            </span>
        </label>
        <input type="text" name="name_on_cccd"
               value="{{ old('name_on_cccd', $user->name) }}"
               class="input input-bordered w-full @error('name_on_cccd') input-error @enderror"
               placeholder="{{ $user->name }}">
        @error('name_on_cccd')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Ngày cấp --}}
    <div>
        <label class="label label-text text-sm font-medium pb-1">
            Ngày cấp <span class="text-error">*</span>
        </label>
        <input type="date" name="issue_date"
               value="{{ old('issue_date') }}"
               max="{{ date('Y-m-d') }}"
               class="input input-bordered w-full @error('issue_date') input-error @enderror">
        @error('issue_date')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
    </div>

</div>

{{-- Divider --}}
<div class="divider text-xs text-base-content/40 my-4">
    Tùy chọn — Upload ảnh để xác minh ngay qua OCR
</div>

{{-- ── Toggle image section ───────────────────────────────────────────────── --}}
<div x-show="!showImageSection" class="mb-4">
    <button type="button"
            @click="showImageSection = true"
            class="btn btn-outline btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Thêm ảnh CCCD để xác minh ngay (Trust Lv 3)
    </button>
    <p class="text-xs text-base-content/40 mt-1.5">
        Nếu không upload ảnh, thông tin sẽ được lưu tạm — có thể xác minh ảnh sau.
    </p>
</div>

{{-- ── Image upload section (ẩn/hiện) ────────────────────────────────────── --}}
<div x-show="showImageSection" x-cloak class="mb-4">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">

        {{-- Mặt trước --}}
        <div>
            <p class="text-xs font-medium text-base-content/70 mb-1.5">
                Ảnh mặt trước
                <span class="text-base-content/40 font-normal">· số CCCD, họ tên, ngày sinh</span>
            </p>
            <label for="img_front"
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
            <input id="img_front" name="front_image" type="file"
                   accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="hidden" @change="onFile($event, 'front')">
            @error('front_image')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Mặt sau --}}
        <div>
            <p class="text-xs font-medium text-base-content/70 mb-1.5">
                Ảnh mặt sau
                <span class="text-base-content/40 font-normal">· ngày cấp (CCCD mới ở mặt sau)</span>
            </p>
            <label for="img_back"
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
            <input id="img_back" name="back_image" type="file"
                   accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="hidden" @change="onFile($event, 'back')">
            @error('back_image')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

    </div>

    {{-- Warning: chỉ 1 ảnh --}}
    <div x-show="hasOne" x-cloak class="alert alert-warning alert-sm py-2 mb-3 text-xs">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Cần chọn cả 2 ảnh để xác minh OCR — hoặc bỏ ảnh đi để lưu text trước.</span>
    </div>

    {{-- Tips --}}
    <div x-show="hasBoth" x-cloak class="mb-3 p-2.5 bg-info/10 border border-info/20 rounded-lg">
        <ul class="text-xs text-base-content/55 list-disc list-inside space-y-0.5">
            <li>Nền tối, ánh sáng đủ — không bị chói hoặc bóng</li>
            <li>Nằm ngang, đủ 4 góc thẻ trong khung, tối thiểu 800×500 px</li>
        </ul>
    </div>

    <button type="button" @click="showImageSection=false; frontFile=false; backFile=false; frontPreview=null; backPreview=null"
            class="btn btn-ghost btn-xs text-base-content/40">
        Bỏ ảnh, lưu text trước →
    </button>
</div>

{{-- ── Security notice ────────────────────────────────────────────────────── --}}
<div class="mb-4 p-2.5 bg-base-200/60 rounded-lg">
    <ul class="text-xs text-base-content/50 list-disc list-inside space-y-0.5">
        <li>Số CCCD lưu dưới dạng hash SHA-256 — không thể truy ngược</li>
        <li>Ảnh chỉ xử lý trong bộ nhớ, không lưu vào máy chủ</li>
        <li>Mỗi số CCCD chỉ liên kết được với 1 tài khoản</li>
    </ul>
</div>

{{-- ── Submit ──────────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 flex-wrap">
    <button type="submit"
            class="btn btn-sm gap-2"
            :class="hasBoth ? 'btn-primary' : 'btn-outline'"
            :disabled="hasOne || loading"
            :class="{ 'btn-disabled': hasOne || loading }">
        <span x-show="!loading">
            <span x-show="hasBoth">Xác minh ngay qua OCR — Trust Lv 3</span>
            <span x-show="!hasBoth">Lưu thông tin, xác minh ảnh sau</span>
        </span>
        <span x-show="loading" class="loading loading-spinner loading-xs"></span>
        <span x-show="loading">
            <span x-show="hasBoth">Đang nhận dạng...</span>
            <span x-show="!hasBoth">Đang lưu...</span>
        </span>
    </button>

    <span class="text-xs text-base-content/40" x-show="hasBoth && !loading">
        Quá trình OCR mất khoảng 5–15 giây
    </span>
    <span class="text-xs text-base-content/40" x-show="!hasBoth && !loading">
        Sẽ lưu ở trạng thái "chờ xác minh ảnh"
    </span>
</div>

</form>
