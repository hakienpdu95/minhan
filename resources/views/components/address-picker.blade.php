<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Tỉnh / Thành phố --}}
    <div class="form-control">
        <label class="label py-0 pb-1.5">
            <span class="label-text font-medium">
                Tỉnh / Thành phố@if($required) <span class="text-error">*</span>@endif
            </span>
        </label>
        <select id="ts-prov-{{ $instanceId }}" name="{{ $nameProvince }}">
            <option value=""></option>
            @foreach ($provinces as $p)
            <option value="{{ $p->province_code }}"
                    {{ $provinceValue === $p->province_code ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        @error($nameProvince)<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
    </div>

    {{-- Phường / Xã --}}
    <div class="form-control">
        <label class="label py-0 pb-1.5">
            <span class="label-text font-medium">
                Phường / Xã@if($required) <span class="text-error">*</span>@endif
            </span>
        </label>
        <select id="ts-ward-{{ $instanceId }}" name="{{ $nameWard }}">
            <option value=""></option>
        </select>
        @error($nameWard)<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
    </div>

    {{-- Số nhà / Tên đường --}}
    @if($showAddress)
    <div class="form-control md:col-span-2">
        <label class="label py-0 pb-1.5">
            <span class="label-text font-medium">Số nhà, tên đường</span>
        </label>
        <input type="text" name="{{ $nameAddress }}" value="{{ $addressValue }}"
               class="input input-bordered input-sm" placeholder="VD: 123 Nguyễn Huệ">
    </div>
    @endif

</div>

{{-- Load TomSelect bundle once even if component is used multiple times on the same page --}}
@once
@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
@endpush
@endonce

{{-- Each instance registers its own picker --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.initOrgAddress(
        'ts-prov-{{ $instanceId }}',
        'ts-ward-{{ $instanceId }}',
        @json($provinceValue ?? ''),
        @json($wardValue ?? '')
    );
});
</script>
@endpush
