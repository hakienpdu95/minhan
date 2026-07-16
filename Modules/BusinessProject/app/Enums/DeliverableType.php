<?php

namespace Modules\BusinessProject\Enums;

/**
 * `deliverables.type` là cột STRING (không phải DB enum) — xem lý do trong migration
 * create_deliverables_table. Enum PHP này chỉ là danh mục giá trị hợp lệ ở tầng ứng dụng,
 * mở rộng dần qua từng Phase mà KHÔNG cần ALTER TABLE.
 */
enum DeliverableType: string
{
    case BusinessContextReport = 'business_context_report';

    // Giai đoạn 3 (Diagnosis Workspace) — 1 deliverable duy nhất/project (Handbook 4.6: "Diagnosis
    // Matrix là tài liệu trung tâm của giai đoạn này"). Content JSON = {overview, findings: [...]}
    // — findings là mảng cấu trúc (Vấn đề/Nguyên nhân gốc/Impact/Effort/Priority tính từ Impact-
    // Effort Matrix, Handbook 4.7), KHÔNG tách bảng riêng vì đây là 1 tài liệu duy nhất theo đúng
    // spec, không cần cross-reference/escalate như Milestone/Issue/Risk.
    case DiagnosisReport = 'diagnosis_report';

    // Giai đoạn 2 (Discovery Workspace) — 5 loại bản ghi khảo sát trực tiếp, mỗi bản ghi
    // tự động là 1 Deliverable CON của Business Discovery Report (parent_id, spec Phần 6.2).
    case Interview = 'interview';
    case Observation = 'observation';
    case DocumentReview = 'document_review';
    case DataReview = 'data_review';
    case ProcessMap = 'process_map';

    // TPS Canvas và Business Discovery Report: mỗi loại 1 deliverable duy nhất/project
    // (parent_id null), nhiều version — xem UpsertSingletonDeliverableAction.
    case TpsCanvas = 'tps_canvas';
    case BusinessDiscoveryReport = 'business_discovery_report';

    // Giai đoạn 4 (Transformation Workspace) — cũng là deliverable singleton/project.
    // Roadmap KHÔNG chứa milestone (bảng riêng `milestones`, xem Modules\BusinessProject\Models\Milestone)
    // — chỉ là bản tổng quan lộ trình, có version theo thời gian.
    case TransformationDesignCanvas = 'transformation_design_canvas';
    case TransformationRoadmap = 'transformation_roadmap';
    case Proposal = 'proposal';
    case Sow = 'sow';

    // Giai đoạn 5 (Delivery Workspace). WeeklyReport: KHÔNG singleton — 1 bản ghi mới mỗi tuần
    // (nhiều deliverable cùng type/project, parent_id null, không qua UpsertSingletonDeliverableAction
    // — xem CreateWeeklyReportAction). MeetingMinutes: 1-1 với 1 Meeting (deliverable_id trên
    // bảng meetings), cũng KHÔNG singleton theo (project,type) vì nhiều Meeting/project.
    case WeeklyReport = 'weekly_report';
    case MeetingMinutes = 'meeting_minutes';

    // Giai đoạn 6 (Closing) — Rule R6: 1 deliverable duy nhất/project, singleton như Discovery
    // Report/Proposal/SOW, đi qua submit/approve chuẩn (Approval Service) nhưng KHÔNG cần confirm
    // (R6 chỉ yêu cầu "đã được phê duyệt", không có bước xác nhận thương mại như Proposal/SOW).
    case FinalReport = 'final_report';

    public function label(): string
    {
        return match ($this) {
            self::BusinessContextReport => 'Business Context Report',
            self::DiagnosisReport => 'Diagnosis Report',
            self::Interview => 'Interview',
            self::Observation => 'Observation',
            self::DocumentReview => 'Document Review',
            self::DataReview => 'Data Review',
            self::ProcessMap => 'Process Map',
            self::TpsCanvas => 'TPS Canvas',
            self::BusinessDiscoveryReport => 'Business Discovery Report',
            self::TransformationDesignCanvas => 'Transformation Design Canvas',
            self::TransformationRoadmap => 'Transformation Roadmap',
            self::Proposal => 'Proposal',
            self::Sow => 'Statement of Work (SOW)',
            self::WeeklyReport => 'Weekly Report',
            self::MeetingMinutes => 'Meeting Minutes',
            self::FinalReport => 'Final Project Report',
        };
    }

    public function workspace(): BusinessProjectStage
    {
        return match ($this) {
            self::BusinessContextReport => BusinessProjectStage::Context,
            self::DiagnosisReport => BusinessProjectStage::Diagnosis,
            self::Interview, self::Observation, self::DocumentReview, self::DataReview, self::ProcessMap,
            self::TpsCanvas, self::BusinessDiscoveryReport => BusinessProjectStage::Discovery,
            self::TransformationDesignCanvas, self::TransformationRoadmap, self::Proposal, self::Sow => BusinessProjectStage::Transformation,
            self::WeeklyReport, self::MeetingMinutes => BusinessProjectStage::Delivery,
            self::FinalReport => BusinessProjectStage::Closing,
        };
    }

    /**
     * 5 loại bản ghi khảo sát trực tiếp trong Discovery Workspace (spec Giai đoạn 2) —
     * dùng để validate `type` khi thêm bản ghi và render dropdown lựa chọn ở form.
     *
     * @return self[]
     */
    public static function discoveryRecordTypes(): array
    {
        return [self::Interview, self::Observation, self::DocumentReview, self::DataReview, self::ProcessMap];
    }
}
