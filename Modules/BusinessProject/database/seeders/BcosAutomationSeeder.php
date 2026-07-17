<?php

namespace Modules\BusinessProject\Database\Seeders;

use App\Shared\Tenancy\Models\Organization;
use Illuminate\Database\Seeder;
use Modules\BusinessProject\Actions\Automation\SeedBcosDefaultWorkflowAction;

/**
 * Backfill cho tổ chức đã tồn tại TRƯỚC khi Workflow Engine tích hợp vào BCOS (Phase 3) —
 * tổ chức tạo SAU thời điểm này tự có qua SeedBcosWorkflowsOnOrganizationCreated listener.
 * Idempotent — chạy lại an toàn (SeedBcosDefaultWorkflowAction tự skip nếu đã tồn tại).
 */
class BcosAutomationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::all('id')->each(function (Organization $org) {
            SeedBcosDefaultWorkflowAction::run($org->id);
        });
    }
}
