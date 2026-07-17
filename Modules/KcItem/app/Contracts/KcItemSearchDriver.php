<?php

namespace Modules\KcItem\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Seam để đổi cơ chế full-text search của Knowledge Center mà không sửa
 * ListKcItemsHandler/Controller — hiện tại chỉ có FullTextKcItemSearchDriver (MySQL FULLTEXT,
 * không cần hạ tầng ngoài). Khi cần scale (VD Meilisearch), thêm 1 class implements interface
 * này + đổi config('kcitem.search.driver'), không đụng code gọi.
 */
interface KcItemSearchDriver
{
    public function apply(Builder $query, string $term): Builder;
}
