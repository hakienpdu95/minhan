<?php

use Illuminate\Support\Facades\Route;
use Modules\Assessment\Http\Controllers\AiImpactController;
use Modules\Assessment\Http\Controllers\AssessmentController;
use Modules\Assessment\Http\Controllers\SandboxAdminController;
use Modules\Assessment\Http\Controllers\AssessmentConfigController;
use Modules\Assessment\Http\Controllers\AssessmentPublicResultController;
use Modules\Assessment\Http\Controllers\AssessmentResultController;
use Modules\Assessment\Http\Controllers\CareerPathwayAdminController;
use Modules\Assessment\Http\Controllers\CareerPathwayController;
use Modules\Assessment\Http\Controllers\CertificationAdminController;
use Modules\Assessment\Http\Controllers\SandboxSessionController;
use Modules\Assessment\Http\Controllers\WorkforceCertificationController;
use Modules\Assessment\Http\Controllers\WorkforceExportController;
use Modules\Assessment\Http\Controllers\WorkforceProfileController;
use Modules\Assessment\Http\Middleware\ValidateAssessmentResultToken;

Route::middleware(['auth', 'verified', 'feature:module.assessment'])->prefix('dashboard/assessments')->name('assessments.')->group(function () {

    Route::get('/',                    [AssessmentController::class, 'index'])->name('index');
    Route::get('/create',              [AssessmentController::class, 'create'])->name('create');
    Route::post('/',                   [AssessmentController::class, 'store'])->name('store');
    Route::get('/{assessment}',        [AssessmentController::class, 'show'])->name('show');
    Route::get('/{assessment}/edit',   [AssessmentController::class, 'edit'])->name('edit');
    Route::put('/{assessment}',        [AssessmentController::class, 'update'])->name('update');
    Route::delete('/{assessment}',     [AssessmentController::class, 'destroy'])->name('destroy');

    Route::prefix('/{assessment}/config')->name('config.')->group(function () {
        Route::get('/',               [AssessmentConfigController::class, 'index'])->name('index');
        Route::get('/data',           [AssessmentConfigController::class, 'getConfig'])->name('get');
        Route::post('/',              [AssessmentConfigController::class, 'saveConfig'])->name('save');
        Route::post('/validate',      [AssessmentConfigController::class, 'validateConfig'])->name('validate');
        Route::get('/snapshots',      [AssessmentConfigController::class, 'snapshots'])->name('snapshots');
        Route::post('/rollback',      [AssessmentConfigController::class, 'rollback'])->name('rollback');
        Route::post('/reprocess',     [AssessmentConfigController::class, 'reprocessAll'])->name('reprocess');
        Route::get('/fields',         [AssessmentConfigController::class, 'getFields'])->name('fields');
        Route::get('/flags',          [AssessmentConfigController::class, 'getFlags'])->name('flags');
        Route::get('/batch/{batchId}',[AssessmentConfigController::class, 'getBatchStatus'])->name('batch-status');
    });

    Route::prefix('/{assessment}/results')->name('results.')->group(function () {
        Route::get('/',                      [AssessmentResultController::class, 'index'])->name('index');
        Route::get('/{result}',              [AssessmentResultController::class, 'show'])->name('show');
        Route::post('/{result}/recalculate', [AssessmentResultController::class, 'recalculate'])->name('recalculate');
        Route::patch('/{result}/feedback',   [AssessmentResultController::class, 'feedback'])->name('feedback');
    });
});

// ── Năng lực số — Workforce Digital Twin ─────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Personal workforce dashboard
    Route::get('/dashboard/workforce/me',  [WorkforceProfileController::class, 'me'])->name('backend.workforce.me');
    Route::post('/dashboard/workforce/me/goal', [WorkforceProfileController::class, 'updateGoal'])->name('backend.workforce.me.goal');

    // Admin: danh sách + chi tiết + export
    Route::prefix('dashboard/workforce')->name('backend.workforce.')->group(function () {
        Route::get('/',                              [WorkforceProfileController::class, 'index'])->name('index');
        Route::get('/api',                           [WorkforceProfileController::class, 'apiIndex'])->name('api');
        Route::get('/export/organization',           [WorkforceExportController::class, 'organizationReport'])->name('export.organization');
        Route::get('/pdf/organization',              [WorkforceExportController::class, 'organizationPdf'])->name('pdf.organization');
        Route::get('/{workforceProfile}',            [WorkforceProfileController::class, 'show'])->name('show');
        Route::post('/{workforceProfile}/recommendations', [WorkforceProfileController::class, 'generateRecommendation'])->name('recommendations.generate');
        Route::get('/{workforceProfile}/export',     [WorkforceExportController::class, 'profileReport'])->name('export.profile');
        Route::get('/{workforceProfile}/pdf',        [WorkforceExportController::class, 'profilePdf'])->name('pdf.profile');
    });

    // AI Sandbox
    Route::prefix('dashboard/sandbox')->name('backend.sandbox.')->group(function () {
        Route::get('/',                                   [SandboxSessionController::class, 'index'])->name('index');
        Route::post('/tasks/{sandboxTask}/start',         [SandboxSessionController::class, 'start'])->name('task.start');
        Route::post('/{sandboxSession}/submit',             [SandboxSessionController::class, 'submit'])->name('submit');
        Route::post('/{sandboxSession}/evaluate',         [SandboxSessionController::class, 'evaluate'])->name('evaluate');
        Route::get('/{sandboxSession}',                   [SandboxSessionController::class, 'show'])->name('show');
    });

    // Certifications
    Route::get('/dashboard/certifications', [WorkforceCertificationController::class, 'index'])
        ->name('backend.certifications.index');

    // Career Pathway — employee view + manual level check
    Route::get('/dashboard/career-pathway', [CareerPathwayController::class, 'index'])
        ->name('backend.career-pathway.index');
    Route::post('/dashboard/career-pathway/check-level', [CareerPathwayController::class, 'checkLevel'])
        ->name('backend.career-pathway.check-level');

    // Career Pathway Admin — CRUD for steps
    Route::prefix('dashboard/career-pathway-admin')->name('backend.career-pathway-admin.')->group(function () {
        Route::get('/',                              [CareerPathwayAdminController::class, 'index'])->name('index');
        Route::get('/create',                        [CareerPathwayAdminController::class, 'create'])->name('create');
        Route::post('/',                             [CareerPathwayAdminController::class, 'store'])->name('store');
        Route::get('/{careerPathwayStep}/edit',      [CareerPathwayAdminController::class, 'edit'])->name('edit');
        Route::put('/{careerPathwayStep}',           [CareerPathwayAdminController::class, 'update'])->name('update');
        Route::delete('/{careerPathwayStep}',        [CareerPathwayAdminController::class, 'destroy'])->name('destroy');
    });

    // Sandbox Admin (content management)
    Route::prefix('dashboard/sandbox-admin')->name('backend.sandbox-admin.')->group(function () {
        Route::get('/',                                                    [SandboxAdminController::class, 'index'])->name('index');
        Route::get('/create',                                             [SandboxAdminController::class, 'createEnv'])->name('env.create');
        Route::post('/',                                                  [SandboxAdminController::class, 'storeEnv'])->name('env.store');
        Route::get('/{sandboxEnvironment}/edit',                         [SandboxAdminController::class, 'editEnv'])->name('env.edit');
        Route::put('/{sandboxEnvironment}',                              [SandboxAdminController::class, 'updateEnv'])->name('env.update');
        Route::get('/{sandboxEnvironment}/tasks',                        [SandboxAdminController::class, 'tasks'])->name('tasks');
        Route::get('/{sandboxEnvironment}/tasks/create',                 [SandboxAdminController::class, 'createTask'])->name('task.create');
        Route::post('/{sandboxEnvironment}/tasks',                       [SandboxAdminController::class, 'storeTask'])->name('task.store');
        Route::get('/tasks/{sandboxTask}/edit',                          [SandboxAdminController::class, 'editTask'])->name('task.edit');
        Route::put('/tasks/{sandboxTask}',                               [SandboxAdminController::class, 'updateTask'])->name('task.update');
        Route::delete('/tasks/{sandboxTask}',                            [SandboxAdminController::class, 'destroyTask'])->name('task.destroy');
    });

    // Certification Admin
    Route::prefix('dashboard/certs-admin')->name('backend.certs-admin.')->group(function () {
        Route::get('/',                                     [CertificationAdminController::class, 'index'])->name('index');
        // Static routes before wildcards
        Route::get('/create',                               [CertificationAdminController::class, 'createDef'])->name('def.create');
        Route::post('/',                                    [CertificationAdminController::class, 'storeDef'])->name('def.store');
        Route::get('/issued',                               [CertificationAdminController::class, 'issued'])->name('issued');
        Route::get('/issue',                                [CertificationAdminController::class, 'issueForm'])->name('issue-form');
        Route::post('/issue',                               [CertificationAdminController::class, 'issue'])->name('issue');
        // Wildcard routes
        Route::get('/{certificationDefinition}/edit',       [CertificationAdminController::class, 'editDef'])->name('def.edit');
        Route::put('/{certificationDefinition}',            [CertificationAdminController::class, 'updateDef'])->name('def.update');
        Route::delete('/{certificationDefinition}',         [CertificationAdminController::class, 'destroyDef'])->name('def.destroy');
        Route::patch('/{workforceCertification}/revoke',    [CertificationAdminController::class, 'revoke'])->name('revoke');
    });

    // AI Impact
    Route::prefix('dashboard/ai-impact')->name('backend.ai-impact.')->group(function () {
        Route::get('/',                               [AiImpactController::class, 'index'])->name('index');
        Route::get('/create',                         [AiImpactController::class, 'create'])->name('create');
        Route::post('/',                              [AiImpactController::class, 'store'])->name('store');
        Route::get('/import',                         [AiImpactController::class, 'importForm'])->name('import-form');
        Route::post('/import',                        [AiImpactController::class, 'import'])->name('import');
        Route::get('/employee/{employee}',            [AiImpactController::class, 'employee'])->name('employee');
        Route::get('/{aiImpactSnapshot}/edit',        [AiImpactController::class, 'edit'])->name('edit');
        Route::put('/{aiImpactSnapshot}',             [AiImpactController::class, 'update'])->name('update');
        Route::delete('/{aiImpactSnapshot}',          [AiImpactController::class, 'destroy'])->name('destroy');
    });
});

// Public result page — không cần auth, validate bằng token
Route::get('/assessment-result/{token}', [AssessmentPublicResultController::class, 'show'])
     ->name('assessment.result.public')
     ->middleware(ValidateAssessmentResultToken::class);
