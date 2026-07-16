<?php

namespace Modules\KcItem\Enums;

enum KcItemType: string
{
    case Document  = 'document';
    case Sop       = 'sop';
    case Video     = 'video';
    case Form      = 'form';
    case Faq       = 'faq';
    case CaseStudy = 'case_study';
    case Policy    = 'policy';

    // BCOS (Business Consulting OS) — Knowledge Workspace, spec Giai đoạn 7: "Case Study/Lessons
    // Learned/Best Practice/Industry Knowledge → lưu vào Knowledge Center (KcItem) với type mới".
    case LessonsLearned    = 'lessons_learned';
    case BestPractice      = 'best_practice';
    case IndustryKnowledge = 'industry_knowledge';

    public function label(): string
    {
        return match ($this) {
            self::Document  => 'Tài liệu',
            self::Sop       => 'SOP',
            self::Video     => 'Video',
            self::Form      => 'Biểu mẫu',
            self::Faq       => 'FAQ',
            self::CaseStudy => 'Case Study',
            self::Policy    => 'Policy/Guideline',
            self::LessonsLearned    => 'Lessons Learned',
            self::BestPractice      => 'Best Practice',
            self::IndustryKnowledge => 'Industry Knowledge',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Document  => 'ti-file-text',
            self::Sop       => 'ti-list-check',
            self::Video     => 'ti-video',
            self::Form      => 'ti-forms',
            self::Faq       => 'ti-help-circle',
            self::CaseStudy => 'ti-bulb',
            self::Policy    => 'ti-scale',
            self::LessonsLearned    => 'ti-brain',
            self::BestPractice      => 'ti-star',
            self::IndustryKnowledge => 'ti-building-factory-2',
        };
    }

    /**
     * 4 type "tri thức dự án" (Handbook Giai đoạn 7) — dùng ở BCOS Knowledge Workspace để lọc
     * dropdown khi tạo Knowledge Asset gắn Business Project, khác các type tài liệu nội bộ chung
     * (document/sop/video/form/faq/policy).
     *
     * @return self[]
     */
    public static function projectKnowledgeTypes(): array
    {
        return [self::CaseStudy, self::LessonsLearned, self::BestPractice, self::IndustryKnowledge];
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'text' => $case->label()],
            self::cases()
        );
    }
}
