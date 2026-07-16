<?php

use Illuminate\Support\Facades\Route;
use Modules\BusinessProject\Http\Controllers\BusinessContextController;
use Modules\BusinessProject\Http\Controllers\BusinessProjectController;
use Modules\BusinessProject\Http\Controllers\ClosingController;
use Modules\BusinessProject\Http\Controllers\CustomerSuccessController;
use Modules\BusinessProject\Http\Controllers\DeliveryController;
use Modules\BusinessProject\Http\Controllers\DiagnosisController;
use Modules\BusinessProject\Http\Controllers\DiscoveryController;
use Modules\BusinessProject\Http\Controllers\KnowledgeController;
use Modules\BusinessProject\Http\Controllers\TransformationController;

// Chưa gắn middleware feature:module.businessproject (Subscription feature gate) ở
// Vertical Slice 1 — BCOS là công cụ nội bộ (Handbook: "không phải sản phẩm bán cho
// doanh nghiệp"), đăng ký feature flag cho Subscription module là việc riêng, ngoài
// phạm vi Vertical Slice 1.
Route::middleware(['auth', 'verified'])->prefix('dashboard/business-projects')->name('backend.business-projects.')->group(function () {
    Route::get('/', [BusinessProjectController::class, 'index'])->name('index');
    Route::get('/{businessProject}', [BusinessProjectController::class, 'show'])->name('show');
    Route::post('/{businessProject}/advance-stage', [BusinessProjectController::class, 'advanceStage'])->name('advance-stage');

    Route::prefix('/{businessProject}/context')->name('context.')->group(function () {
        Route::post('/', [BusinessContextController::class, 'store'])->name('store');
        Route::put('/', [BusinessContextController::class, 'update'])->name('update');
        Route::post('/submit', [BusinessContextController::class, 'submit'])->name('submit');
        Route::post('/approve', [BusinessContextController::class, 'approve'])->name('approve');
        Route::post('/reject', [BusinessContextController::class, 'reject'])->name('reject');
    });

    Route::prefix('/{businessProject}/discovery')->name('discovery.')->group(function () {
        Route::get('/', [DiscoveryController::class, 'show'])->name('show');
        Route::post('/records', [DiscoveryController::class, 'storeRecord'])->name('records.store');
        Route::post('/tps-canvas', [DiscoveryController::class, 'saveTpsCanvas'])->name('tps-canvas.save');
        Route::post('/report', [DiscoveryController::class, 'saveReport'])->name('report.save');
    });

    Route::prefix('/{businessProject}/diagnosis')->name('diagnosis.')->group(function () {
        Route::get('/', [DiagnosisController::class, 'show'])->name('show');
        Route::post('/overview', [DiagnosisController::class, 'saveOverview'])->name('overview.save');
        Route::post('/findings', [DiagnosisController::class, 'addFinding'])->name('findings.store');
        Route::delete('/findings/{index}', [DiagnosisController::class, 'removeFinding'])->name('findings.destroy');
        Route::post('/evidence', [DiagnosisController::class, 'attachEvidence'])->name('evidence.attach');
        Route::post('/submit', [DiagnosisController::class, 'submit'])->name('submit');
        Route::post('/approve', [DiagnosisController::class, 'approve'])->name('approve');
        Route::post('/reject', [DiagnosisController::class, 'reject'])->name('reject');
    });

    Route::prefix('/{businessProject}/transformation')->name('transformation.')->group(function () {
        Route::get('/', [TransformationController::class, 'show'])->name('show');
        Route::post('/canvas', [TransformationController::class, 'saveCanvas'])->name('canvas.save');
        Route::post('/roadmap', [TransformationController::class, 'saveRoadmap'])->name('roadmap.save');
        Route::post('/milestones', [TransformationController::class, 'storeMilestone'])->name('milestones.store');
        Route::post('/proposal', [TransformationController::class, 'saveProposal'])->name('proposal.save');
        Route::post('/sow', [TransformationController::class, 'saveSow'])->name('sow.save');

        Route::prefix('/{type}')->where(['type' => 'proposal|sow'])->group(function () {
            Route::post('/submit', [TransformationController::class, 'submit'])->name('submit');
            Route::post('/approve', [TransformationController::class, 'approve'])->name('approve');
            Route::post('/reject', [TransformationController::class, 'reject'])->name('reject');
            Route::post('/confirm', [TransformationController::class, 'confirm'])->name('confirm');
        });
    });

    Route::prefix('/{businessProject}/delivery')->name('delivery.')->group(function () {
        Route::get('/', [DeliveryController::class, 'show'])->name('show');

        Route::post('/meetings', [DeliveryController::class, 'storeMeeting'])->name('meetings.store');
        Route::post('/meetings/{meeting}/minutes', [DeliveryController::class, 'saveMeetingMinutes'])->name('meetings.minutes.save');

        Route::post('/weekly-reports', [DeliveryController::class, 'addWeeklyReport'])->name('weekly-reports.store');

        Route::post('/issues', [DeliveryController::class, 'storeIssue'])->name('issues.store');
        Route::post('/issues/{issue}/escalate', [DeliveryController::class, 'escalateIssue'])->name('issues.escalate');

        Route::post('/risks', [DeliveryController::class, 'storeRisk'])->name('risks.store');
        Route::post('/risks/{risk}/escalate', [DeliveryController::class, 'escalateRisk'])->name('risks.escalate');

        Route::post('/change-requests/{changeRequest}/submit', [DeliveryController::class, 'submitChangeRequest'])->name('change-requests.submit');
        Route::post('/change-requests/{changeRequest}/approve', [DeliveryController::class, 'approveChangeRequest'])->name('change-requests.approve');
        Route::post('/change-requests/{changeRequest}/reject', [DeliveryController::class, 'rejectChangeRequest'])->name('change-requests.reject');

        Route::post('/tasks/attach', [DeliveryController::class, 'attachTask'])->name('tasks.attach');
    });

    Route::prefix('/{businessProject}/closing')->name('closing.')->group(function () {
        Route::get('/', [ClosingController::class, 'show'])->name('show');
        Route::post('/final-report', [ClosingController::class, 'saveFinalReport'])->name('final-report.save');
        Route::post('/final-report/submit', [ClosingController::class, 'submitFinalReport'])->name('final-report.submit');
        Route::post('/final-report/approve', [ClosingController::class, 'approveFinalReport'])->name('final-report.approve');
        Route::post('/final-report/reject', [ClosingController::class, 'rejectFinalReport'])->name('final-report.reject');
        Route::post('/knowledge-assets/attach', [ClosingController::class, 'attachKnowledgeAsset'])->name('knowledge-assets.attach');
    });

    Route::prefix('/{businessProject}/knowledge')->name('knowledge.')->group(function () {
        Route::get('/', [KnowledgeController::class, 'show'])->name('show');
        Route::post('/attach', [KnowledgeController::class, 'attach'])->name('attach');
    });

    Route::prefix('/{businessProject}/customer-success')->name('customer-success.')->group(function () {
        Route::get('/', [CustomerSuccessController::class, 'show'])->name('show');
        Route::post('/survey/attach', [CustomerSuccessController::class, 'attachSurvey'])->name('survey.attach');
        Route::post('/notes', [CustomerSuccessController::class, 'storeNote'])->name('notes.store');
        Route::post('/reviews/{successReview}/follow-up-done', [CustomerSuccessController::class, 'markFollowUpDone'])->name('reviews.follow-up-done');
        Route::post('/lead', [CustomerSuccessController::class, 'createLead'])->name('lead.create');
    });
});
