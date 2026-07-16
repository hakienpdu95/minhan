<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\View\View;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\BusinessProjectMember;
use Modules\BusinessProject\Models\BusinessProjectStageHistory;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\SuccessReview;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Modules\KcItem\Models\KcItem;
use Modules\KcItem\Models\KcViewLog;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Phần 10 spec — "BCOS tự đo chính nó": BCOS Admin Dashboard, chỉ Founder/Admin truy cập, cross-
 * project (khác Dashboard cá nhân và Dashboard 1 project cụ thể). Mọi KPI tính từ dữ liệu ĐÃ CÓ
 * (Phần 6), không thêm bảng thống kê song song — 1 ngoại lệ: `business_project_stage_history`
 * (xem migration) là bổ sung Ở NGUỒN cho "Average Cycle Time", đúng nguyên tắc spec tự nêu, không
 * phải bảng KPI song song.
 */
class BcosDashboardController extends Controller
{
    public function show(CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);

        $orgId = TenantContext::getOrganizationId();

        return view('businessproject::bcos-dashboard.show', [
            'gateCompliance' => $this->gateComplianceRows($orgId, $handler)['summary'],
            'knowledgeReuse' => $this->knowledgeReuseRows($orgId)['summary'],
            'cycleTime' => $this->cycleTimeRows($orgId)['rows'],
            'deliverableDiscipline' => $this->deliverableDisciplineRows($orgId)['summary'],
            'csatNps' => $this->csatNpsRows($orgId)['summary'],
            'r7Fulfillment' => $this->r7FulfillmentRows($orgId)['summary'],
        ]);
    }

    public function exportGateCompliance(CheckStageGateEligibilityHandler $handler)
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->gateComplianceRows($orgId, $handler)['rows']))
            ->download('bcos-gate-compliance-'.now()->format('Ymd').'.csv');
    }

    public function exportKnowledgeReuse()
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->knowledgeReuseRows($orgId)['rows']))
            ->download('bcos-knowledge-reuse-'.now()->format('Ymd').'.csv');
    }

    public function exportCycleTime()
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->cycleTimeRows($orgId)['rows']))
            ->download('bcos-cycle-time-'.now()->format('Ymd').'.csv');
    }

    public function exportDeliverableDiscipline()
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->deliverableDisciplineRows($orgId)['rows']))
            ->download('bcos-deliverable-discipline-'.now()->format('Ymd').'.csv');
    }

    public function exportCsatNps()
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->csatNpsRows($orgId)['rows']))
            ->download('bcos-csat-nps-'.now()->format('Ymd').'.csv');
    }

    public function exportR7Fulfillment()
    {
        $this->authorize('viewBcosDashboard', BusinessProject::class);
        $orgId = TenantContext::getOrganizationId();

        return (new FastExcel($this->r7FulfillmentRows($orgId)['rows']))
            ->download('bcos-r7-fulfillment-'.now()->format('Ymd').'.csv');
    }

    /**
     * KPI 1 — Gate Compliance Rate: % project KHÔNG bị "trễ" (đủ điều kiện qua gate ≥7 ngày mà
     * chưa ai bấm chuyển) trong tổng số project đang active (chưa closed/cancelled).
     */
    private function gateComplianceRows(int $orgId, CheckStageGateEligibilityHandler $handler): array
    {
        $stuckDaysThreshold = 7;

        $projects = BusinessProject::withoutTenant()
            ->where('organization_id', $orgId)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->get();

        $rows = collect();
        $stuck = 0;

        foreach ($projects as $project) {
            $result = $handler->handle(new CheckStageGateEligibilityQuery($project));
            $daysSinceUpdate = $project->updated_at->diffInDays(now());
            $isStuck = $result->canAdvance && $daysSinceUpdate >= $stuckDaysThreshold;
            if ($isStuck) {
                $stuck++;
            }

            $rows->push([
                'Mã dự án' => $project->code,
                'Tên dự án' => $project->name,
                'Giai đoạn hiện tại' => $project->current_stage->label(),
                'Đủ điều kiện qua gate' => $result->canAdvance ? 'Có' : 'Chưa',
                'Số ngày chưa cập nhật' => $daysSinceUpdate,
                'Trễ (>= '.$stuckDaysThreshold.' ngày)' => $isStuck ? 'Có' : 'Không',
            ]);
        }

        $total = $projects->count();

        return [
            'summary' => [
                'total_active' => $total,
                'stuck' => $stuck,
                'rate' => $total > 0 ? round(($total - $stuck) / $total * 100, 1) : null,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * KPI 2 — Knowledge Reuse Rate: ƯỚC TÍNH (không có bảng ghi "tra cứu theo project" — xem
     * research note) qua kc_view_logs: KcItem có business_project_id (đã publish từ 1 project)
     * được xem bởi user thuộc business_project_members của MỘT project KHÁC, sau created_at.
     */
    private function knowledgeReuseRows(int $orgId): array
    {
        $originated = KcItem::withoutTenant()
            ->where('organization_id', $orgId)
            ->whereNotNull('business_project_id')
            ->with('businessProject:id,code,name')
            ->get(['id', 'title', 'business_project_id', 'created_at']);

        $rows = collect();
        $reusedCount = 0;

        foreach ($originated as $item) {
            $otherProjectMemberIds = BusinessProjectMember::whereHas(
                'businessProject',
                fn ($q) => $q->where('organization_id', $orgId)
            )
                ->where('business_project_id', '!=', $item->business_project_id)
                ->pluck('user_id')
                ->unique();

            $reusedByOther = $otherProjectMemberIds->isNotEmpty() && KcViewLog::where('item_id', $item->id)
                ->where('viewed_at', '>', $item->created_at)
                ->whereIn('user_id', $otherProjectMemberIds)
                ->exists();

            if ($reusedByOther) {
                $reusedCount++;
            }

            $rows->push([
                'Knowledge Asset' => $item->title,
                'Dự án gốc' => $item->businessProject?->code ?? '—',
                'Được tra cứu ở dự án khác (ước tính)' => $reusedByOther ? 'Có' : 'Chưa',
            ]);
        }

        $total = $originated->count();

        return [
            'summary' => [
                'total' => $total,
                'reused' => $reusedCount,
                'rate' => $total > 0 ? round($reusedCount / $total * 100, 1) : null,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * KPI 3 — Average Cycle Time theo giai đoạn (business_project_stage_history — chỉ có dữ
     * liệu từ khi bảng này bắt đầu ghi, không backfill được lịch sử advance-stage trước đó).
     */
    private function cycleTimeRows(int $orgId): array
    {
        $histories = BusinessProjectStageHistory::where('organization_id', $orgId)
            ->orderBy('business_project_id')
            ->orderBy('changed_at')
            ->get()
            ->groupBy('business_project_id');

        $durationsByStage = [];

        foreach ($histories as $projectId => $rowsForProject) {
            $project = BusinessProject::withoutTenant()->find($projectId);
            if (! $project) {
                continue;
            }

            $prevTimestamp = $project->created_at;
            foreach ($rowsForProject as $row) {
                if ($row->stage_from) {
                    $seconds = $prevTimestamp->diffInSeconds($row->changed_at);
                    $durationsByStage[$row->stage_from][] = $seconds;
                }
                $prevTimestamp = $row->changed_at;
            }
        }

        $rows = collect();
        foreach (BusinessProjectStage::ordered() as $stage) {
            $values = $durationsByStage[$stage->value] ?? [];
            $avgDays = count($values) > 0 ? round((array_sum($values) / count($values)) / 86400, 1) : null;

            $rows->push([
                'Giai đoạn' => $stage->label(),
                'Số dự án đã đi qua (từ khi có dữ liệu)' => count($values),
                'Thời gian trung bình (ngày)' => $avgDays ?? 'Chưa đủ dữ liệu',
            ]);
        }

        return ['rows' => $rows];
    }

    /**
     * KPI 4 — Deliverable Version Discipline: % deliverable `confirmed` có >= 2 version (chứng
     * minh có review/sửa thật). Sub-metric "% dùng Template chuẩn" BỎ QUA — cột `template_id`
     * chưa tồn tại trong schema (Template Library chưa xây, Phase 2 mảng 5/5).
     */
    private function deliverableDisciplineRows(int $orgId): array
    {
        $confirmed = Deliverable::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('status', DeliverableStatus::Confirmed->value)
            ->withCount('versions')
            ->with('businessProject:id,code')
            ->get();

        $rows = $confirmed->map(fn ($d) => [
            'Dự án' => $d->businessProject?->code ?? '—',
            'Loại Deliverable' => $d->type,
            'Số version' => $d->versions_count,
            'Có review thật (>=2 version)' => $d->versions_count >= 2 ? 'Có' : 'Không',
        ]);

        $total = $confirmed->count();
        $multiVersion = $confirmed->where('versions_count', '>=', 2)->count();

        return [
            'summary' => [
                'total_confirmed' => $total,
                'multi_version' => $multiVersion,
                'rate' => $total > 0 ? round($multiVersion / $total * 100, 1) : null,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * KPI 5 — CSAT/NPS trung bình + Renewal Rate, từ success_reviews (Giai đoạn 8).
     */
    private function csatNpsRows(int $orgId): array
    {
        $reviews = SuccessReview::withoutTenant()
            ->where('organization_id', $orgId)
            ->with('businessProject:id,code')
            ->get();

        $rows = $reviews->map(fn ($r) => [
            'Dự án' => $r->businessProject?->code ?? '—',
            'Ngày ghi nhận' => $r->created_at->format('d/m/Y'),
            'CSAT' => $r->csat_score !== null ? (int) $r->csat_score : '—',
            'NPS' => $r->nps_score !== null ? (int) $r->nps_score : '—',
            'Renewal' => $r->renewal_status->label(),
        ]);

        $csatValues = $reviews->pluck('csat_score')->filter(fn ($v) => $v !== null);
        $npsValues = $reviews->pluck('nps_score')->filter(fn ($v) => $v !== null);

        $decided = $reviews->filter(fn ($r) => in_array($r->renewal_status->value, ['renewed', 'lost'], true));
        $renewed = $decided->filter(fn ($r) => $r->renewal_status->value === 'renewed');

        return [
            'summary' => [
                'csat_avg' => $csatValues->isNotEmpty() ? round($csatValues->avg(), 2) : null,
                'nps_avg' => $npsValues->isNotEmpty() ? round($npsValues->avg(), 2) : null,
                'renewal_decided' => $decided->count(),
                'renewal_rate' => $decided->isNotEmpty() ? round($renewed->count() / $decided->count() * 100, 1) : null,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * KPI 6 — R7 Fulfillment Rate: % project đã closed có >= 1 Knowledge Asset, kèm số lượng
     * trung bình/project (không chỉ đạt điều kiện tối thiểu).
     */
    private function r7FulfillmentRows(int $orgId): array
    {
        $closed = BusinessProject::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('status', 'closed')
            ->withCount('kcItems')
            ->get();

        $rows = $closed->map(fn ($p) => [
            'Mã dự án' => $p->code,
            'Tên dự án' => $p->name,
            'Số Knowledge Asset' => $p->kc_items_count,
            'Đạt R7 (>=1)' => $p->kc_items_count >= 1 ? 'Có' : 'Không',
        ]);

        $total = $closed->count();
        $withAsset = $closed->where('kc_items_count', '>=', 1)->count();

        return [
            'summary' => [
                'total_closed' => $total,
                'with_asset' => $withAsset,
                'rate' => $total > 0 ? round($withAsset / $total * 100, 1) : null,
                'avg_assets_per_project' => $total > 0 ? round($closed->avg('kc_items_count'), 2) : null,
            ],
            'rows' => $rows,
        ];
    }
}
