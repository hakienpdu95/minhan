{{-- Shared form partial --}}
<div class="card bg-base-100 shadow-sm border border-base-200 max-w-2xl"
     x-data="{
         name: {{ Js::from(old('name', $assessment->name ?? '')) }},
         slugify(text) {
             return text.toLowerCase()
                 .normalize('NFD').replace(/[̀-ͯ]/g, '')
                 .replace(/đ/g, 'd')
                 .replace(/[^a-z0-9]+/g, '-')
                 .replace(/^-|-$/g, '');
         },
         get preview() {
             const s = this.slugify(this.name);
             return s ? s + '-xxxxxxxx' : 'tự động tạo từ tên...';
         }
     }">
    <div class="card-body space-y-4">

        <div class="form-control">
            <label class="label py-0 pb-1.5"><span class="label-text font-medium">Tên hiển thị <span class="text-error">*</span></span></label>
            <input type="text" name="name" x-model="name"
                   class="input input-bordered input-sm @error('name') input-error @enderror"
                   placeholder="Digital Maturity Assessment">
            @error('name')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="form-control">
            <label class="label py-0 pb-1.5"><span class="label-text font-medium">Assessment Code</span></label>
            @isset($assessment)
            <input type="text" value="{{ $assessment->assessment_code }}"
                   class="input input-bordered input-sm font-mono bg-base-200" readonly>
            <span class="label-text-alt text-base-content/40 mt-1">Không thể thay đổi sau khi tạo.</span>
            @else
            <div class="input input-bordered input-sm font-mono bg-base-200 flex items-center text-base-content/50"
                 x-text="preview">
            </div>
            <span class="label-text-alt text-base-content/40 mt-1">Tự động tạo từ tên, có hash ngẫu nhiên để đảm bảo không trùng.</span>
            @endisset
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Aggregation Model</span></label>
                <select name="aggregation_model" class="select select-bordered select-sm">
                    @foreach(['weighted_domain' => 'Weighted Domain', 'flat_sum' => 'Flat Sum', 'sectioned' => 'Sectioned'] as $v => $l)
                    <option value="{{ $v }}" {{ old('aggregation_model', $assessment->aggregation_model ?? 'weighted_domain') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Classification Type</span></label>
                <select name="classification_type" class="select select-bordered select-sm">
                    @foreach(['score_band' => 'Score Band', 'pass_fail' => 'Pass / Fail', 'persona_match' => 'Persona Match', 'none' => 'None'] as $v => $l)
                    <option value="{{ $v }}" {{ old('classification_type', $assessment->classification_type ?? 'score_band') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="has_scoring" value="1" class="checkbox checkbox-sm"
                       {{ old('has_scoring', $assessment->has_scoring ?? true) ? 'checked' : '' }}>
                <span class="label-text">Bật chấm điểm</span>
            </label>
            @isset($assessment)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm"
                       {{ old('is_active', $assessment->is_active ?? true) ? 'checked' : '' }}>
                <span class="label-text">Active</span>
            </label>
            @endisset
        </div>

    </div>
</div>

<div class="flex gap-3 mt-4">
    <button type="submit" class="btn btn-primary btn-sm">{{ isset($assessment) ? 'Cập nhật' : 'Tạo Assessment' }}</button>
    <a href="{{ route('assessments.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
</div>
