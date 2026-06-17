@extends('layouts.backend')
@section('title', 'Nhật ký tiến độ')

@section('content')
<div>
    <div class="mb-5">
        <h1 class="text-2xl font-bold">Nhật ký tiến độ</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ $vertical->label() }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4"><span>{{ session('success') }}</span></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Log form --}}
        <div>
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body">
                    <h2 class="font-semibold text-sm mb-3">Ghi nhận tiến độ</h2>
                    <form method="POST" action="{{ route('deployment.progress.store', ['vertical' => $vertical->code()]) }}">
                        @csrf
                        <div class="form-control mb-3">
                            <label class="label py-1"><span class="label-text text-xs">Đối tượng</span></label>
                            <select name="deployment_target_id" class="select select-bordered select-sm">
                                @foreach($targets as $t)
                                <option value="{{ $t->id }}" @selected($currentTarget?->id == $t->id)>
                                    {{ $t->targetOrganization?->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control mb-3">
                            <label class="label py-1"><span class="label-text text-xs">Phase</span></label>
                            <input type="text" name="phase"
                                   value="{{ old('phase', $currentTarget?->current_phase) }}"
                                   class="input input-bordered input-sm">
                        </div>
                        <div class="form-control mb-3">
                            <label class="label py-1"><span class="label-text text-xs">% hoàn thành</span></label>
                            <input type="number" name="percent" min="0" max="100"
                                   value="{{ old('percent', 0) }}"
                                   class="input input-bordered input-sm">
                        </div>
                        <div class="form-control mb-3">
                            <label class="label py-1"><span class="label-text text-xs">Ghi chú</span></label>
                            <textarea name="remark" rows="3" class="textarea textarea-bordered textarea-sm">{{ old('remark') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full">Ghi nhận</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Log list --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-4 border-b border-base-200">
                        <h2 class="font-semibold text-sm">
                            Lịch sử
                            @if($currentTarget)
                            — {{ $currentTarget->targetOrganization?->name }}
                            @endif
                        </h2>
                    </div>
                    @forelse($logs as $log)
                    <div class="px-4 py-3 border-b border-base-200 last:border-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-outline badge-sm">{{ $log->phase }}</span>
                                <span class="font-medium text-sm">{{ $log->percent }}%</span>
                            </div>
                            <span class="text-xs text-base-content/40">
                                {{ $log->loggedBy?->name }} · {{ $log->logged_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        @if($log->remark)
                        <p class="text-sm text-base-content/70 mt-1">{{ $log->remark }}</p>
                        @endif
                    </div>
                    @empty
                    <div class="p-8 text-center text-base-content/40 text-sm">
                        Chưa có nhật ký nào. Chọn đối tượng và ghi nhận tiến độ.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
