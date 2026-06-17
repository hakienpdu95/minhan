@extends('layouts.backend')
@section('title', 'Tạo dự án — ' . $vertical->label())

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <h1 class="text-2xl font-bold">Tạo dự án mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">
            Vertical: <span class="badge badge-outline badge-sm">{{ $vertical->code() }}</span>
        </p>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body">
            <form method="POST"
                  action="{{ route('deployment.projects.store', ['vertical' => $vertical->code()]) }}">
                @csrf

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tên dự án <span class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="input input-bordered @error('name') input-error @enderror"
                           placeholder="Ví dụ: Triển khai Q3-2026">
                    @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Mã dự án <span class="text-error">*</span></span></label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           class="input input-bordered input-sm @error('code') input-error @enderror"
                           placeholder="Ví dụ: TXNG-Q3-2026"
                           style="text-transform:uppercase">
                    @error('code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Trạng thái</span></label>
                    <select name="status" class="select select-bordered select-sm">
                        @foreach(\Modules\Project\Enums\ProjectStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(old('status', 'planning') === $s->value)>
                            {{ $s->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Ngày bắt đầu</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="input input-bordered input-sm">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Ngày kết thúc</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="input input-bordered input-sm">
                    </div>
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Mô tả</span></label>
                    <textarea name="description" rows="3"
                              class="textarea textarea-bordered text-sm"
                              placeholder="Mô tả ngắn về mục tiêu dự án...">{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-sm">Tạo dự án</button>
                    <a href="{{ route('deployment.projects.index', ['vertical' => $vertical->code()]) }}"
                       class="btn btn-ghost btn-sm">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
