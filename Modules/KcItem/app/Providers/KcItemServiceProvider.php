<?php

namespace Modules\KcItem\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\KcItem\Console\Commands\ExpireKcItemsCommand;
use Modules\KcItem\Contracts\KcItemSearchDriver;
use Modules\KcItem\Models\KcItem;
use Modules\KcItem\Models\KcTag;
use Modules\KcItem\Policies\KcItemPolicy;
use Modules\KcItem\Policies\KcTagPolicy;
use Modules\KcItem\Search\FullTextKcItemSearchDriver;
use Modules\KcItem\Search\MeilisearchKcItemSearchDriver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class KcItemServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'KcItem';

    protected string $nameLower = 'kcitem';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    protected array $commands = [
        ExpireKcItemsCommand::class,
    ];

    public function boot(): void
    {
        // registerConfig() (chạy trong parent::boot()) merge config/config.php trước — module
        // config KHÔNG có sẵn ở register() (nwidart chỉ merge config ở boot(), khác quy ước
        // thường thấy), nên binding đọc config phải đặt ở đây, sau parent::boot().
        parent::boot();

        $this->app->bind(KcItemSearchDriver::class, match (config('kcitem.search.driver', 'fulltext')) {
            'meilisearch' => MeilisearchKcItemSearchDriver::class,
            default => FullTextKcItemSearchDriver::class,
        });

        Gate::policy(KcItem::class, KcItemPolicy::class);
        Gate::policy(KcTag::class, KcTagPolicy::class);

        // Đăng ký thêm abilities cho status transitions (không thuộc CRUD chuẩn)
        Gate::define('submit-kc-item',  fn ($user, $item) => (new KcItemPolicy)->submit($user, $item));
        Gate::define('approve-kc-item', fn ($user, $item) => (new KcItemPolicy)->approve($user, $item));
        Gate::define('reject-kc-item',  fn ($user, $item) => (new KcItemPolicy)->reject($user, $item));
        Gate::define('archive-kc-item', fn ($user, $item) => (new KcItemPolicy)->archive($user, $item));
    }
}
