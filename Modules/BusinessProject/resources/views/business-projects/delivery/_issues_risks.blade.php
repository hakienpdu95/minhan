{{--
    Issue / Risk — ghi nhận trong workspace, nghiêm trọng thì escalate thành Change Request
    (spec Giai đoạn 5). Biến: $businessProject, $issues (Issue[]), $risks (Risk[]).
--}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Issues --}}
            <div>
                <h2 class="font-semibold mb-3">Vấn đề phát sinh ({{ $issues->count() }})</h2>
                <form action="{{ route('backend.business-projects.delivery.issues.store', $businessProject) }}" method="POST" class="space-y-2 mb-3">
                    @csrf
                    <input type="text" name="title" class="input input-bordered input-sm w-full" placeholder="Tiêu đề vấn đề phát sinh" required>
                    <div class="flex gap-2">
                        <select name="severity" class="select select-bordered select-sm flex-1">
                            @foreach(\Modules\BusinessProject\Enums\IssueSeverity::cases() as $severity)
                            <option value="{{ $severity->value }}" @selected($severity->value === 'medium')>{{ $severity->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm">Thêm</button>
                    </div>
                    <textarea name="description" rows="1" class="textarea textarea-bordered textarea-sm w-full" placeholder="Mô tả (tùy chọn)"></textarea>
                </form>

                @forelse($issues as $issue)
                <div class="border border-base-200 rounded-lg p-2 mb-2 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium">{{ $issue->title }}</span>
                        <div class="flex gap-1">
                            <span class="badge badge-xs {{ $issue->severity->badgeClass() }}">{{ $issue->severity->label() }}</span>
                            <span class="badge badge-xs {{ $issue->status->badgeClass() }}">{{ $issue->status->label() }}</span>
                        </div>
                    </div>
                    @if($issue->description)<p class="text-base-content/60 mb-1">{{ $issue->description }}</p>@endif

                    @if($issue->status->value === 'open')
                    <details>
                        <summary class="link link-error text-xs cursor-pointer">Chuyển thành Yêu cầu thay đổi</summary>
                        <form action="{{ route('backend.business-projects.delivery.issues.escalate', ['businessProject' => $businessProject, 'issue' => $issue->id]) }}" method="POST" class="space-y-1.5 mt-2">
                            @csrf
                            <input type="text" name="title" class="input input-bordered input-xs w-full" placeholder="Tiêu đề Change Request" required>
                            <textarea name="description" rows="1" class="textarea textarea-bordered textarea-xs w-full" placeholder="Mô tả thay đổi cần thực hiện"></textarea>
                            <label class="flex items-center gap-1.5 text-xs">
                                <input type="checkbox" name="impacts_scope" value="1" class="checkbox checkbox-xs">
                                Ảnh hưởng phạm vi hợp đồng (SOW)
                            </label>
                            <button type="submit" class="btn btn-error btn-xs">Xác nhận chuyển</button>
                        </form>
                    </details>
                    @endif
                </div>
                @empty
                <p class="text-xs text-base-content/40">Chưa có vấn đề phát sinh nào.</p>
                @endforelse
            </div>

            {{-- Risks --}}
            <div>
                <h2 class="font-semibold mb-3">Rủi ro ({{ $risks->count() }})</h2>
                <form action="{{ route('backend.business-projects.delivery.risks.store', $businessProject) }}" method="POST" class="space-y-2 mb-3">
                    @csrf
                    <input type="text" name="title" class="input input-bordered input-sm w-full" placeholder="Tiêu đề rủi ro" required>
                    <div class="flex gap-2">
                        <select name="likelihood" class="select select-bordered select-sm flex-1">
                            @foreach(\Modules\BusinessProject\Enums\RiskLikelihood::cases() as $likelihood)
                            <option value="{{ $likelihood->value }}" @selected($likelihood->value === 'medium')>Khả năng xảy ra: {{ $likelihood->label() }}</option>
                            @endforeach
                        </select>
                        <select name="impact" class="select select-bordered select-sm flex-1">
                            @foreach(\Modules\BusinessProject\Enums\RiskImpact::cases() as $impact)
                            <option value="{{ $impact->value }}" @selected($impact->value === 'medium')>Tác động: {{ $impact->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-outline btn-sm">Thêm</button>
                    </div>
                    <textarea name="description" rows="1" class="textarea textarea-bordered textarea-sm w-full" placeholder="Mô tả (tùy chọn)"></textarea>
                </form>

                @forelse($risks as $risk)
                <div class="border border-base-200 rounded-lg p-2 mb-2 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium">{{ $risk->title }}</span>
                        <div class="flex gap-1">
                            <span class="badge badge-xs">KN: {{ $risk->likelihood->label() }}</span>
                            <span class="badge badge-xs">TĐ: {{ $risk->impact->label() }}</span>
                            <span class="badge badge-xs {{ $risk->status->badgeClass() }}">{{ $risk->status->label() }}</span>
                        </div>
                    </div>
                    @if($risk->description)<p class="text-base-content/60 mb-1">{{ $risk->description }}</p>@endif

                    @if($risk->status->value === 'open')
                    <details>
                        <summary class="link link-error text-xs cursor-pointer">Chuyển thành Yêu cầu thay đổi</summary>
                        <form action="{{ route('backend.business-projects.delivery.risks.escalate', ['businessProject' => $businessProject, 'risk' => $risk->id]) }}" method="POST" class="space-y-1.5 mt-2">
                            @csrf
                            <input type="text" name="title" class="input input-bordered input-xs w-full" placeholder="Tiêu đề Change Request" required>
                            <textarea name="description" rows="1" class="textarea textarea-bordered textarea-xs w-full" placeholder="Mô tả thay đổi cần thực hiện"></textarea>
                            <label class="flex items-center gap-1.5 text-xs">
                                <input type="checkbox" name="impacts_scope" value="1" class="checkbox checkbox-xs">
                                Ảnh hưởng phạm vi hợp đồng (SOW)
                            </label>
                            <button type="submit" class="btn btn-error btn-xs">Xác nhận chuyển</button>
                        </form>
                    </details>
                    @endif
                </div>
                @empty
                <p class="text-xs text-base-content/40">Chưa có rủi ro nào.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
