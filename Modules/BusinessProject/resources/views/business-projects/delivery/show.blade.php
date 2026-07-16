@extends('layouts.backend')

@section('title', $businessProject->name.' — Delivery')

@section('content')
<div>
    @include('businessproject::business-projects._partials.project-header', ['businessProject' => $businessProject])

    @if(session('success'))
    <div class="alert alert-success mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            @include('businessproject::business-projects.delivery._tasks', ['businessProject' => $businessProject, 'tasks' => $tasks])
            @include('businessproject::business-projects.delivery._meetings', ['businessProject' => $businessProject, 'meetings' => $meetings])
            @include('businessproject::business-projects.delivery._weekly_reports', ['businessProject' => $businessProject, 'weeklyReports' => $weeklyReports])
            @include('businessproject::business-projects.delivery._issues_risks', ['businessProject' => $businessProject, 'issues' => $issues, 'risks' => $risks])
            @include('businessproject::business-projects.delivery._change_requests', ['businessProject' => $businessProject, 'changeRequests' => $changeRequests])
        </div>

        <div class="space-y-4">
            @include('businessproject::business-projects._partials.gate-checklist', [
                'gateResult' => $gateResult,
                'businessProject' => $businessProject,
            ])

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <h3 class="font-semibold text-sm mb-2">Thành viên dự án</h3>
                    <ul class="space-y-1.5">
                        @foreach($businessProject->members as $member)
                        <li class="flex items-center justify-between text-xs">
                            <span>{{ $member->user->name }}</span>
                            <span class="badge badge-xs">{{ $member->project_role->label() }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
