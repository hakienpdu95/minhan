@extends('layouts.backend')

@section('title', 'Lên lịch phỏng vấn')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4 pb-0">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.recruitment.candidates.index') }}">Ứng viên</a></li>
        <li><a href="{{ route('backend.recruitment.applications.show', $application) }}">{{ $application->candidate?->full_name }}</a></li>
        <li class="font-semibold">Lên lịch phỏng vấn</li>
    </ul>
</div>
@endsection

@section('content')
<div x-data="rcInterviewCreate" class="p-6 max-w-2xl">

    <h1 class="text-xl font-bold mb-1">Lên lịch phỏng vấn</h1>
    <p class="text-sm opacity-60 mb-5">Ứng viên: <strong>{{ $application->candidate?->full_name }}</strong></p>

    <form method="POST" action="{{ route('backend.recruitment.applications.interviews.store', $application) }}">
        @csrf

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5 space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Loại phỏng vấn</span></label>
                        <select name="interview_type" class="select select-bordered" required>
                            @foreach($interviewTypes as $type)
                            <option value="{{ $type['value'] }}" {{ old('interview_type', 'video') === $type['value'] ? 'selected' : '' }}>
                                {{ $type['text'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Stage liên quan</span></label>
                        <select name="stage_id" class="select select-bordered" required>
                            @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ $application->current_stage_id == $stage->id ? 'selected' : '' }}>
                                {{ $stage->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Tiêu đề (tùy chọn)</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="VD: Phỏng vấn kỹ thuật vòng 1"
                           class="input input-bordered" maxlength="200">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Thời gian bắt đầu</span></label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                               class="input input-bordered" required>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Thời lượng (phút)</span></label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}"
                               min="15" max="480" class="input input-bordered" required>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Địa điểm</span></label>
                    <input type="text" name="location" value="{{ old('location') }}"
                           placeholder="Phòng họp A / Online" class="input input-bordered" maxlength="300">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Meeting URL</span></label>
                        <input type="url" name="meeting_url" value="{{ old('meeting_url') }}"
                               placeholder="https://meet.google.com/..." class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Meeting ID</span></label>
                        <input type="text" name="meeting_id" value="{{ old('meeting_id') }}"
                               class="input input-bordered" maxlength="100">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Ghi chú nội bộ</span></label>
                    <textarea name="interviewer_note" class="textarea textarea-bordered" rows="2"
                              placeholder="Lưu ý cho panel...">{{ old('interviewer_note') }}</textarea>
                </div>

                {{-- Panel Assignment --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-medium text-sm">Panel phỏng vấn</label>
                        <button type="button" @click="addPanelist()" class="btn btn-ghost btn-xs">+ Thêm</button>
                    </div>

                    <template x-for="(panelist, idx) in panelists" :key="idx">
                        <div class="flex gap-2 mb-2 items-center">
                            <select :name="'panelists[' + idx + '][user_id]'" class="select select-bordered select-sm flex-1" x-model="panelist.user_id">
                                <option value="">— Chọn người —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <select :name="'panelists[' + idx + '][role]'" class="select select-bordered select-sm w-44" x-model="panelist.role">
                                @foreach($panelistRoles as $role)
                                <option value="{{ $role['value'] }}">{{ $role['text'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="removePanelist(idx)" class="btn btn-ghost btn-xs text-error">✕</button>
                        </div>
                    </template>

                    <p x-show="panelists.length === 0" class="text-xs opacity-40">Chưa có panelist nào</p>
                </div>

            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-error mt-4">
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex gap-3 mt-5">
            <button type="submit" class="btn btn-primary">Tạo lịch phỏng vấn</button>
            <a href="{{ route('backend.recruitment.applications.show', $application) }}" class="btn btn-ghost">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('rcInterviewCreate', function() {
        return {
            panelists: [{ user_id: '', role: 'interviewer' }],
            addPanelist: function() {
                this.panelists.push({ user_id: '', role: 'interviewer' });
            },
            removePanelist: function(idx) {
                this.panelists.splice(idx, 1);
            },
        };
    });
});
</script>
@endpush
