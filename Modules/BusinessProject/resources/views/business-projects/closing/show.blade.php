@extends('layouts.backend')

@section('title', $businessProject->name.' — Closing')

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
            @include('businessproject::business-projects.closing._final_report', ['businessProject' => $businessProject, 'finalReport' => $finalReport])
            @include('businessproject::business-projects.closing._knowledge_assets', [
                'businessProject' => $businessProject,
                'knowledgeAssets' => $knowledgeAssets,
                'attachableKcItems' => $attachableKcItems,
            ])
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
