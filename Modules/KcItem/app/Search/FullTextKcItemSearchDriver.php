<?php

namespace Modules\KcItem\Search;

use Illuminate\Database\Eloquent\Builder;
use Modules\KcItem\Contracts\KcItemSearchDriver;

/**
 * MySQL FULLTEXT (InnoDB) qua MATCH...AGAINST boolean mode — không cần hạ tầng ngoài (không
 * Meilisearch/Scout). Đủ dùng cho quy mô Knowledge Center nội bộ; nâng lên driver khác khi cần
 * scale chỉ cần thêm 1 class implements KcItemSearchDriver + đổi config('kcitem.search.driver').
 *
 * Giới hạn biết trước: `innodb_ft_min_token_size` mặc định của MySQL = 3 ký tự — từ tiếng Việt
 * ngắn hơn (là, và, có...) không được lập chỉ mục nên không match được qua FULLTEXT; fallback
 * LIKE bên dưới chỉ xử lý trường hợp toàn bộ câu tìm kiếm không còn token nào đủ dài, không bù
 * được từng từ ngắn lẫn trong câu dài hơn.
 */
class FullTextKcItemSearchDriver implements KcItemSearchDriver
{
    private const COLUMNS = ['kc_items.title', 'kc_items.summary', 'kc_items.content'];

    public function apply(Builder $query, string $term): Builder
    {
        $boolean = $this->toBooleanQuery($term);

        if ($boolean === '') {
            // Không còn token nào đủ dài để MATCH...AGAINST — fallback LIKE thay vì trả rỗng oan.
            return $query->where(function (Builder $sub) use ($term): void {
                $like = '%' . $term . '%';
                $sub->where('kc_items.title', 'like', $like)
                    ->orWhere('kc_items.summary', 'like', $like);
            });
        }

        return $query->whereFullText(self::COLUMNS, $boolean, ['mode' => 'boolean']);
    }

    /**
     * Free text → boolean-mode query: mỗi từ ≥3 ký tự thành `+từ*` (bắt buộc khớp, prefix
     * match). Loại ký tự có ý nghĩa đặc biệt trong boolean mode để tránh lỗi cú pháp
     * MATCH...AGAINST khi user gõ các ký tự đó.
     */
    private function toBooleanQuery(string $term): string
    {
        $words = preg_split('/\s+/u', trim($term)) ?: [];

        $tokens = [];
        foreach ($words as $word) {
            $clean = preg_replace('/[+\-><()~*"@]/u', '', $word);
            if ($clean !== null && mb_strlen($clean) >= 3) {
                $tokens[] = '+' . $clean . '*';
            }
        }

        return implode(' ', $tokens);
    }
}
