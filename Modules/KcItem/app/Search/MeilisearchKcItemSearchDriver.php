<?php

namespace Modules\KcItem\Search;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Modules\KcItem\Contracts\KcItemSearchDriver;
use Modules\KcItem\Models\KcItem;

/**
 * Driver Meilisearch (Laravel Scout) — chỉ hoạt động khi `KC_SEARCH_DRIVER=meilisearch` VÀ
 * `SCOUT_DRIVER=meilisearch` (xem `.env`, `config/scout.php`). KcItem là single source of truth
 * (MySQL) — driver chỉ dùng Meilisearch để tìm ID khớp full-text, sau đó `whereIn` lại trên chính
 * `kc_items` để mọi filter/sort/pagination còn lại của `ListKcItemsHandler` áp dụng như bình
 * thường lên dữ liệu MySQL thật (không trả nội dung thẳng từ index).
 *
 * Không tự áp relevance ordering (FIELD(...)) — cùng hành vi với FullTextKcItemSearchDriver
 * (chỉ lọc, không sắp xếp), để 2 driver đối xứng: đổi driver qua config không đổi thứ tự kết quả
 * mặc định mà Consultant đã quen.
 */
class MeilisearchKcItemSearchDriver implements KcItemSearchDriver
{
    private const MAX_MATCHES = 1000;

    public function apply(Builder $query, string $term): Builder
    {
        $ids = KcItem::search($term)
            ->where('organization_id', (int) TenantContext::getOrganizationId())
            ->take(self::MAX_MATCHES)
            ->keys();

        return $query->whereIn('kc_items.id', $ids);
    }
}
