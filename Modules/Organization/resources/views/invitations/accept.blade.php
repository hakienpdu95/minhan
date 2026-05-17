@extends('layouts.backend')
@section('title', 'Chấp nhận lời mời')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center">
    <div class="card bg-base-100 shadow-lg border border-base-200 w-full max-w-md">
        <div class="card-body text-center gap-4">
            <div class="flex justify-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                </div>
            </div>

            <div>
                <p class="text-base-content/60 text-sm mb-1">Bạn được mời tham gia</p>
                <h1 class="text-2xl font-bold text-base-content">{{ $invitation->organization->name }}</h1>
                <p class="mt-2">với vai trò <span class="badge badge-primary">{{ $invitation->role }}</span></p>
            </div>

            @if ($invitation->expires_at)
            <p class="text-xs text-base-content/40">Hết hạn: {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
            @endif

            <div class="flex flex-col gap-2 mt-2">
                <form method="POST" action="{{ route('organization.invitations.confirm', $invitation->token) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">Chấp nhận lời mời</button>
                </form>
                <a href="{{ route('backend.dashboard') }}" class="btn btn-ghost btn-sm">Từ chối</a>
            </div>
        </div>
    </div>
</div>
@endsection
