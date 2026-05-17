<?php

use Illuminate\Support\Facades\Route;
use Modules\Organization\Actions\AcceptInvitationAction;
use Modules\Organization\Http\Controllers\OrganizationController;
use Modules\Organization\Models\OrganizationInvitation;

/*
|--------------------------------------------------------------------------
| Organization Module — Web Routes
|--------------------------------------------------------------------------
*/

// ── Backend CRUD (admin panel) ─────────────────────────────────────────
Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {
    Route::resource('organizations', OrganizationController::class);
});

// ── Invitation accept/confirm (shareable links) ────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/join/{token}', function (string $token) {
        $invitation = OrganizationInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with('organization')
            ->firstOrFail();

        return view('organization::invitations.accept', compact('invitation'));
    })->name('organization.invitations.accept');

    Route::post('/join/{token}', function (string $token) {
        $invitation = OrganizationInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        AcceptInvitationAction::run($invitation, request()->user());

        return redirect()->route('backend.dashboard')
            ->with('success', 'Bạn đã tham gia tổ chức ' . $invitation->organization->name);
    })->name('organization.invitations.confirm');
});
