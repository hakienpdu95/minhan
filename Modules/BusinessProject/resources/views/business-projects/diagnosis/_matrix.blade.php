{{--
    Diagnosis Matrix (Handbook 4.6) — Vấn đề | Nguyên nhân gốc | Tác động | Mức độ ưu tiên |
    Bằng chứng. Priority tính tự động từ Impact + Effort (Impact–Effort Matrix, Handbook 4.7),
    không nhập tay. Biến: $businessProject, $diagnosisReport (Deliverable|null), $categories,
    $impacts, $efforts (enum cases).
--}}
@php
    $findings = $diagnosisReport?->versions->first()?->content['findings'] ?? [];
@endphp

<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="font-semibold mb-3">Diagnosis Matrix ({{ count($findings) }})</h2>

        <form action="{{ route('backend.business-projects.diagnosis.findings.store', $businessProject) }}" method="POST" class="space-y-3 mb-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Vấn đề</label>
                    <input type="text" name="problem" class="input input-bordered input-sm w-full" placeholder="VD: Doanh số giảm" required>
                </div>
                <div>
                    <label class="label label-text text-sm font-medium">Nhóm</label>
                    <select name="category" class="select select-bordered select-sm w-full">
                        @foreach($categories as $category)
                        <option value="{{ $category->value }}">{{ $category->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="label label-text text-sm font-medium">Nguyên nhân gốc</label>
                <input type="text" name="root_cause" class="input input-bordered input-sm w-full" placeholder="VD: Thiếu quy trình tư vấn thống nhất">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label label-text text-sm font-medium">Tác động (Impact)</label>
                    <select name="impact" class="select select-bordered select-sm w-full">
                        @foreach($impacts as $impact)
                        <option value="{{ $impact->value }}">{{ $impact->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label label-text text-sm font-medium">Effort</label>
                    <select name="effort" class="select select-bordered select-sm w-full">
                        @foreach($efforts as $effort)
                        <option value="{{ $effort->value }}">{{ $effort->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-outline btn-sm">Thêm finding</button>
        </form>

        @if(count($findings) > 0)
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Vấn đề</th>
                        <th>Nhóm</th>
                        <th>Nguyên nhân gốc</th>
                        <th>Impact</th>
                        <th>Priority</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($findings as $index => $finding)
                    <tr>
                        <td class="text-xs">{{ $finding['problem'] }}</td>
                        <td class="text-xs">{{ \Modules\BusinessProject\Enums\DiagnosisCategory::from($finding['category'])->label() }}</td>
                        <td class="text-xs">{{ $finding['root_cause'] }}</td>
                        <td class="text-xs">{{ \Modules\BusinessProject\Enums\DiagnosisImpact::from($finding['impact'])->label() }}</td>
                        <td>
                            @php $priority = \Modules\BusinessProject\Enums\DiagnosisPriority::from($finding['priority']); @endphp
                            <span class="badge badge-xs {{ $priority->badgeClass() }}">{{ $priority->label() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('backend.business-projects.diagnosis.findings.destroy', ['businessProject' => $businessProject, 'index' => $index]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs text-error">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-xs text-base-content/40">Chưa có finding nào.</p>
        @endif
    </div>
</div>
