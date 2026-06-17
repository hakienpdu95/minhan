<?php

namespace Modules\Deployment\Services;

class GapAnalysisService
{
    private const RECOMMENDATIONS = [
        'legal' => [
            'title'   => 'Hoàn thiện hồ sơ pháp lý',
            'actions' => [
                'Thu thập đầy đủ giấy phép kinh doanh và giấy tờ pháp lý còn thiếu.',
                'Lập danh sách kiểm tra (checklist) các loại giấy tờ bắt buộc cho từng xã viên.',
                'Sắp xếp hệ thống lưu trữ tài liệu pháp lý theo thứ tự và dễ tra cứu.',
            ],
        ],
        'hr' => [
            'title'   => 'Nâng cao năng lực nhân sự',
            'actions' => [
                'Xác định và bổ nhiệm người phụ trách dữ liệu (data focal point) chính thức.',
                'Tổ chức buổi giới thiệu hệ thống cho ban lãnh đạo trước khi triển khai.',
                'Đảm bảo ít nhất 2 nhân sự có thể vận hành hệ thống trên thiết bị di động.',
            ],
        ],
        'infra' => [
            'title'   => 'Cải thiện hạ tầng kỹ thuật',
            'actions' => [
                'Kiểm tra và nâng cấp kết nối Internet tại địa điểm sản xuất chính.',
                'Chuẩn bị ít nhất 1 smartphone (Android 9+ hoặc iOS 13+) được dành riêng cho hệ thống.',
                'Khảo sát và đánh dấu các khu vực có tín hiệu GPS yếu để có phương án xử lý.',
            ],
        ],
        'process' => [
            'title'   => 'Chuẩn hóa quy trình vận hành',
            'actions' => [
                'Ghi chép và tài liệu hóa quy trình sản xuất/vận hành hiện tại.',
                'Chuẩn hóa biểu mẫu thu thập dữ liệu sản xuất trước khi chuyển sang hệ thống số.',
                'Thiết lập lịch cập nhật dữ liệu định kỳ (tuần/tháng) và phân công người thực hiện.',
            ],
        ],
    ];

    private const DOMAIN_LABELS = [
        'legal'   => 'Pháp lý & Giấy tờ',
        'hr'      => 'Nhân sự & Năng lực',
        'infra'   => 'Hạ tầng & Công nghệ',
        'process' => 'Quy trình & Dữ liệu',
    ];

    /**
     * @param  array<string, array{score: int, ...}> $domainScores
     * @return array<array{domain: string, label: string, score: int, priority: string, title: string, actions: string[]}>
     */
    public function analyze(array $domainScores): array
    {
        if (empty($domainScores)) return [];

        // Sort by score ascending (weakest first)
        asort($domainScores);

        $gaps = [];
        foreach ($domainScores as $code => $data) {
            $score    = $data['score'];
            $priority = $this->priority($score);

            if ($priority === null) continue; // score ≥ 80 → no gap

            $rec = self::RECOMMENDATIONS[$code] ?? [
                'title'   => "Cải thiện domain {$code}",
                'actions' => ['Xem xét và cải thiện các chỉ tiêu trong lĩnh vực này.'],
            ];

            $gaps[] = [
                'domain'   => $code,
                'label'    => self::DOMAIN_LABELS[$code] ?? $code,
                'score'    => $score,
                'priority' => $priority,
                'title'    => $rec['title'],
                'actions'  => $rec['actions'],
            ];
        }

        return array_slice($gaps, 0, 3); // top 3 gaps
    }

    private function priority(int $score): ?string
    {
        return match (true) {
            $score < 40  => 'high',
            $score < 60  => 'medium',
            $score < 80  => 'low',
            default      => null,
        };
    }
}
