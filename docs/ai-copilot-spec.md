# AiCopilot Module — Đặc tả kỹ thuật v1.0.0

> **Scope**: Tích hợp AI API để thực hiện các tác vụ chỉ định trong hệ thống, có tracking chi tiết và giới hạn sử dụng theo gói subscription.
>
> **Pattern**: AVSA + CQRS-lite — nhất quán với Branch, Leave, KpiGoal.
>
> **Status**: Spec sẵn sàng implement · Chưa có code.

---

## 1. Mục tiêu & Phạm vi

### 1.1 Mục tiêu

| # | Mục tiêu |
|---|----------|
| 1 | Tích hợp nhiều AI provider (Claude, OpenAI…) qua một driver abstraction duy nhất |
| 2 | Định nghĩa các **AI Task** gắn với context nghiệp vụ cụ thể (SOP, KPI, HR, Lead…) |
| 3 | Quản lý **Prompt Library** — system prompts + template biến động per org |
| 4 | Ghi log mọi AI request: input/output tokens, cost, latency, subject model |
| 5 | Enforce quota `quota.ai_requests` và `quota.ai_tokens` theo gói subscription |
| 6 | Dashboard usage cho AI_OP và CEO |

### 1.2 Ngoài phạm vi (v1)

- Chat tự do (general chatbot) — để v2
- Fine-tuning / RAG với vector DB
- AI trả lời real-time qua SSE / streaming (v2)
- Tự động trigger AI theo schedule (dùng WorkflowAutomation)

---

## 2. Khái niệm cốt lõi

```
AiAgent          Cấu hình một "robot AI" cho một task cụ thể
                 (model, provider, system prompt, nhiệt độ, giới hạn token)

AiPrompt         Template prompt gắn với một Agent
                 Hỗ trợ biến {{employee_name}}, {{kpi_value}}…

AiRequest        Bản ghi mỗi lần gọi AI API (audit trail bất biến)

AiMonthlyUsage   Tổng hợp usage theo tháng per org (denorm, dùng cho quota bar)
```

### 2.1 Luồng thực hiện một AI Task

```
[Controller / Action trigger]
       │
       ▼
ExecuteAiTaskAction          ← entry point duy nhất
  ├─ 1. Check module gate:   org_can('module.ai')
  ├─ 2. Check quota:         org_at_limit('quota.ai_requests', current_month_count)
  ├─ 3. Load Agent + Prompt  (cache per request)
  ├─ 4. Render prompt        (replace {{variables}})
  ├─ 5. Dispatch AiRequestJob → queue 'ai'
  │       OR call inline if agent->sync_mode = true
  ├─ 6. Record AiRequest     (status=pending → processing → done/failed)
  └─ 7. Return AiRequest UUID cho frontend poll hoặc trả kết quả ngay
```

---

## 3. Database Schema

### 3.1 `ai_agents`

Định nghĩa các "agent" AI trong hệ thống. Có hai loại:
- **system** (`is_system = true`): ship cùng module, không thể xóa
- **custom** (`is_system = false`): org tự tạo thêm

```sql
CREATE TABLE ai_agents (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid                CHAR(36) UNIQUE NOT NULL,
  organization_id     BIGINT UNSIGNED NULL,        -- NULL = system-level (shared)
  name                VARCHAR(120) NOT NULL,
  slug                VARCHAR(80) NOT NULL,         -- unique per org, e.g. sop.step_draft
  description         TEXT NULL,
  task_type           VARCHAR(60) NOT NULL,         -- enum: sop|kpi|hr|lead|email|general
  provider            VARCHAR(30) NOT NULL DEFAULT 'claude',  -- claude|openai|gemini
  model               VARCHAR(80) NOT NULL,         -- claude-sonnet-4-6, gpt-4o…
  temperature         DECIMAL(3,2) DEFAULT 0.70,
  max_tokens          SMALLINT UNSIGNED DEFAULT 1024,
  timeout_seconds     TINYINT UNSIGNED DEFAULT 30,
  sync_mode           TINYINT(1) DEFAULT 0,        -- 0=queue, 1=inline (nhanh <3s)
  is_active           TINYINT(1) DEFAULT 1,
  is_system           TINYINT(1) DEFAULT 0,
  created_by          BIGINT UNSIGNED NULL,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP,
  deleted_at          TIMESTAMP NULL,

  INDEX idx_agents_org_slug (organization_id, slug),
  INDEX idx_agents_task_type (task_type)
);
```

**System agents ship sẵn** (seeder):

| slug | task_type | model | Mô tả |
|------|-----------|-------|-------|
| `sop.step_draft` | sop | claude-sonnet-4-6 | Draft nội dung một bước SOP |
| `sop.summarize` | sop | claude-haiku-4-5 | Tóm tắt toàn bộ SOP |
| `kpi.analysis` | kpi | claude-sonnet-4-6 | Phân tích KPI, gợi ý cải thiện |
| `hr.feedback_draft` | hr | claude-sonnet-4-6 | Draft feedback đánh giá nhân viên |
| `hr.job_description` | hr | claude-sonnet-4-6 | Viết JD từ tiêu chí |
| `lead.score_analysis` | lead | claude-haiku-4-5 | Phân tích và chấm điểm lead |
| `email.draft` | email | claude-haiku-4-5 | Draft email chuyên nghiệp |
| `general.summarize` | general | claude-haiku-4-5 | Tóm tắt văn bản bất kỳ |
| `general.translate` | general | claude-haiku-4-5 | Dịch thuật đa ngôn ngữ |

---

### 3.2 `ai_prompts`

Template prompt gắn với agent. Một agent có thể có nhiều prompt version; dùng `is_default = true` để chọn active.

```sql
CREATE TABLE ai_prompts (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid                CHAR(36) UNIQUE NOT NULL,
  organization_id     BIGINT UNSIGNED NULL,         -- NULL = system prompt
  agent_id            BIGINT UNSIGNED NOT NULL,
  name                VARCHAR(120) NOT NULL,
  description         TEXT NULL,
  system_prompt       LONGTEXT NOT NULL,            -- system message
  user_template       LONGTEXT NOT NULL,            -- template với {{variables}}
  variables_schema    JSON NULL,                    -- [{"key":"employee_name","type":"string","required":true}]
  is_default          TINYINT(1) DEFAULT 0,
  is_active           TINYINT(1) DEFAULT 1,
  version             TINYINT UNSIGNED DEFAULT 1,
  created_by          BIGINT UNSIGNED NULL,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP,
  deleted_at          TIMESTAMP NULL,

  INDEX idx_prompts_agent_default (agent_id, is_default),
  FOREIGN KEY (agent_id) REFERENCES ai_agents(id)
);
```

**Template syntax** (Mustache-like, xử lý bằng `str_replace`):
```
Bạn là chuyên gia SOP. Hãy soạn thảo bước sau:
Tiêu đề: {{step_title}}
Phòng ban: {{department}}
Yêu cầu thêm: {{user_note}}
```

---

### 3.3 `ai_requests`

Log bất biến mỗi lần gọi AI. Không UPDATE sau khi `status = done | failed`.

```sql
CREATE TABLE ai_requests (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid                CHAR(36) UNIQUE NOT NULL,
  organization_id     BIGINT UNSIGNED NOT NULL,
  user_id             BIGINT UNSIGNED NOT NULL,
  agent_id            BIGINT UNSIGNED NOT NULL,
  prompt_id           BIGINT UNSIGNED NULL,

  -- Subject (polymorphic — AI được gọi từ đâu)
  subject_type        VARCHAR(150) NULL,           -- e.g. Modules\Sop\Models\SopStep
  subject_id          BIGINT UNSIGNED NULL,

  -- Request content
  rendered_prompt     LONGTEXT NULL,               -- Prompt sau khi render biến
  input_variables     JSON NULL,                   -- {key: value} đã truyền vào

  -- Response
  ai_output           LONGTEXT NULL,
  finish_reason       VARCHAR(30) NULL,            -- stop|max_tokens|error

  -- Metrics
  provider            VARCHAR(30) NOT NULL,
  model               VARCHAR(80) NOT NULL,
  input_tokens        INT UNSIGNED DEFAULT 0,
  output_tokens       INT UNSIGNED DEFAULT 0,
  total_tokens        INT UNSIGNED DEFAULT 0,
  cost_usd            DECIMAL(10,6) DEFAULT 0,
  duration_ms         INT UNSIGNED NULL,

  -- Lifecycle
  status              ENUM('pending','processing','done','failed') DEFAULT 'pending',
  error_message       TEXT NULL,
  queued_at           TIMESTAMP NULL,
  started_at          TIMESTAMP NULL,
  completed_at        TIMESTAMP NULL,

  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP,

  INDEX idx_req_org_created (organization_id, created_at),
  INDEX idx_req_user (user_id),
  INDEX idx_req_agent (agent_id),
  INDEX idx_req_subject (subject_type, subject_id),
  INDEX idx_req_status (status),
  FOREIGN KEY (agent_id) REFERENCES ai_agents(id)
);
```

> **Không có `deleted_at`** — request log là immutable audit trail.

---

### 3.4 `ai_monthly_usages`

Denormalized aggregate — reset đầu tháng. Dùng cho quota bar và dashboard.

```sql
CREATE TABLE ai_monthly_usages (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id     BIGINT UNSIGNED NOT NULL,
  year_month          CHAR(7) NOT NULL,            -- '2026-06'
  agent_id            BIGINT UNSIGNED NULL,        -- NULL = tổng cộng
  task_type           VARCHAR(60) NULL,
  total_requests      INT UNSIGNED DEFAULT 0,
  successful_requests INT UNSIGNED DEFAULT 0,
  total_input_tokens  BIGINT UNSIGNED DEFAULT 0,
  total_output_tokens BIGINT UNSIGNED DEFAULT 0,
  total_tokens        BIGINT UNSIGNED DEFAULT 0,
  total_cost_usd      DECIMAL(12,6) DEFAULT 0,
  updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uq_usage_org_month_agent (organization_id, year_month, agent_id),
  INDEX idx_usage_org_month (organization_id, year_month)
);
```

---

## 4. Quota & Subscription Integration

### 4.1 Feature keys (thêm vào Subscription plans)

| Feature slug | Type | Starter | Growth | Scale | Enterprise |
|---|---|---|---|---|---|
| `module.ai` | bool | `false` | `true` | `true` | `true` |
| `quota.ai_requests` | int/month | 0 | 200 | 2000 | 0 (unlimited) |
| `quota.ai_tokens` | int/month | 0 | 500_000 | 5_000_000 | 0 |
| `limit.ai_agents` | int | 0 | 5 | 20 | 0 |

> `quota.ai_requests` đã có sẵn trong `config/subscription.php`. Cần thêm `quota.ai_tokens` và `limit.ai_agents`.

### 4.2 Gate checks trong ExecuteAiTaskAction

```php
// 1. Module enabled?
if (!org_can('module.ai')) {
    throw new FeatureNotAvailableException('module.ai');
}

// 2. Request quota
$monthRequests = AiMonthlyUsage::currentMonth($orgId)->sum('total_requests');
if (org_at_limit('quota.ai_requests', $monthRequests)) {
    throw new QuotaExceededException('quota.ai_requests');
}

// 3. Token quota (ước tính trước khi gọi, check lại sau)
$monthTokens = AiMonthlyUsage::currentMonth($orgId)->sum('total_tokens');
if (org_at_limit('quota.ai_tokens', $monthTokens)) {
    throw new QuotaExceededException('quota.ai_tokens');
}
```

### 4.3 Recording usage (sau khi AI trả về)

```php
// Dùng DB transaction + pessimistic lock (pattern từ Subscription module)
DB::transaction(function () use ($orgId, $agentId, $taskType, $tokens, $cost, $yearMonth) {
    // Upsert monthly total
    AiMonthlyUsage::lockForUpdate()
        ->updateOrCreate(
            ['organization_id' => $orgId, 'year_month' => $yearMonth, 'agent_id' => null],
            []
        )
        ->increment('total_requests')
        ->incrementBy('total_tokens', $tokens)
        ->incrementBy('total_cost_usd', $cost);

    // Upsert per-agent breakdown
    AiMonthlyUsage::lockForUpdate()
        ->updateOrCreate(
            ['organization_id' => $orgId, 'year_month' => $yearMonth, 'agent_id' => $agentId],
            ['task_type' => $taskType]
        )
        ->increment('total_requests')
        ->incrementBy('total_tokens', $tokens);
});

// Notify SubscriptionContext để flush cache
RecordFeatureUsageAction::run($org, 'quota.ai_requests', 1);
RecordFeatureUsageAction::run($org, 'quota.ai_tokens', $tokens);
```

---

## 5. Provider Abstraction

### 5.1 Contract

```php
// Modules/AiCopilot/app/Drivers/Contracts/AiDriverContract.php
interface AiDriverContract
{
    public function complete(AiCompletionRequest $request): AiCompletionResult;

    /** Ước tính token count (không gọi API) */
    public function estimateTokens(string $text): int;

    /** Giá per 1M tokens [input, output] theo model */
    public function pricing(string $model): array; // ['input' => 3.00, 'output' => 15.00]
}
```

### 5.2 DTOs

```php
// AiCompletionRequest
readonly class AiCompletionRequest
{
    public function __construct(
        public string  $model,
        public string  $systemPrompt,
        public string  $userMessage,
        public float   $temperature  = 0.7,
        public int     $maxTokens    = 1024,
        public int     $timeoutSec   = 30,
    ) {}
}

// AiCompletionResult
readonly class AiCompletionResult
{
    public function __construct(
        public string  $content,
        public string  $finishReason,   // stop|max_tokens
        public int     $inputTokens,
        public int     $outputTokens,
        public int     $totalTokens,
        public float   $costUsd,
        public int     $durationMs,
    ) {}
}
```

### 5.3 Implementations

```
Modules/AiCopilot/app/Drivers/
├── ClaudeDriver.php    ← Anthropic SDK (anthropic-php/sdk)
├── OpenAiDriver.php    ← openai-php/laravel
└── MockDriver.php      ← Testing (trả về fixed content)
```

**ClaudeDriver.php** (tóm tắt):
```php
class ClaudeDriver implements AiDriverContract
{
    public function complete(AiCompletionRequest $req): AiCompletionResult
    {
        $start = microtime(true);

        $response = $this->client->messages()->create([
            'model'      => $req->model,
            'max_tokens' => $req->maxTokens,
            'system'     => $req->systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $req->userMessage]],
        ]);

        $input  = $response->usage->inputTokens;
        $output = $response->usage->outputTokens;
        [$inPrice, $outPrice] = array_values($this->pricing($req->model));

        return new AiCompletionResult(
            content:      $response->content[0]->text,
            finishReason: $response->stopReason,
            inputTokens:  $input,
            outputTokens: $output,
            totalTokens:  $input + $output,
            costUsd:      ($input * $inPrice + $output * $outPrice) / 1_000_000,
            durationMs:   (int) ((microtime(true) - $start) * 1000),
        );
    }

    public function pricing(string $model): array
    {
        return match(true) {
            str_contains($model, 'opus')   => ['input' => 15.00, 'output' => 75.00],
            str_contains($model, 'sonnet') => ['input' =>  3.00, 'output' => 15.00],
            default                        => ['input' =>  0.25, 'output' =>  1.25], // haiku
        };
    }
}
```

### 5.4 AiDriverManager (Service Locator)

```php
class AiDriverManager
{
    /** @var array<string, AiDriverContract> */
    private array $resolved = [];

    public function driver(string $provider): AiDriverContract
    {
        return $this->resolved[$provider] ??= match($provider) {
            'claude' => new ClaudeDriver(config('ai_copilot.providers.claude.api_key')),
            'openai' => new OpenAiDriver(config('ai_copilot.providers.openai.api_key')),
            'mock'   => new MockDriver(),
            default  => throw new \InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }
}
```

Bind trong ServiceProvider:
```php
$this->app->singleton(AiDriverManager::class);
```

---

## 6. Actions (AVSA Layer)

### 6.1 ExecuteAiTaskAction — Entry Point

```php
// Modules/AiCopilot/app/Actions/ExecuteAiTaskAction.php
class ExecuteAiTaskAction
{
    use AsAction;

    public function handle(
        string   $agentSlug,
        array    $variables,         // ['step_title' => '...', 'department' => '...']
        ?Model   $subject = null,    // SopStep, Employee, KpiGoal…
        bool     $forceSync = false,
    ): AiRequest {
        $org   = TenantContext::resolve();
        $user  = auth()->user();
        $agent = $this->loadAgent($agentSlug, $org->id);
        $prompt = $agent->defaultPrompt();

        // Gate checks
        $this->checkGates($org);

        // Render template
        $rendered = $this->renderTemplate($prompt->user_template, $variables);

        // Create pending request record
        $aiRequest = AiRequest::create([
            'uuid'            => Str::uuid(),
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'agent_id'        => $agent->id,
            'prompt_id'       => $prompt->id,
            'subject_type'    => $subject ? get_class($subject) : null,
            'subject_id'      => $subject?->getKey(),
            'rendered_prompt' => $rendered,
            'input_variables' => $variables,
            'provider'        => $agent->provider,
            'model'           => $agent->model,
            'status'          => 'pending',
        ]);

        // Dispatch
        if ($agent->sync_mode || $forceSync) {
            ProcessAiRequestJob::dispatchSync($aiRequest->id);
        } else {
            ProcessAiRequestJob::dispatch($aiRequest->id)
                ->onQueue('ai')
                ->afterCommit();
        }

        return $aiRequest->fresh();
    }

    private function checkGates(Organization $org): void
    {
        if (!org_can('module.ai')) {
            throw new FeatureNotAvailableException('module.ai');
        }

        $ctx = SubscriptionContext::get();
        $monthUsage = AiMonthlyUsage::currentMonth($org->id)->value('total_requests') ?? 0;

        if (org_at_limit('quota.ai_requests', $monthUsage)) {
            throw new QuotaExceededException('quota.ai_requests', org_limit('quota.ai_requests'));
        }
    }

    private function renderTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{{$key}}}", e((string) $value), $template);
        }
        return $template;
    }
}
```

### 6.2 ProcessAiRequestJob — Queue Worker

```php
// Modules/AiCopilot/app/Jobs/ProcessAiRequestJob.php
class ProcessAiRequestJob extends TenantAwareJob
{
    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(private readonly int $aiRequestId)
    {
        parent::__construct();
        $this->onQueue('ai');
    }

    public function handle(AiDriverManager $manager): void
    {
        $this->withTenant(function () use ($manager) {
            $request = AiRequest::findOrFail($this->aiRequestId);

            if ($request->status !== 'pending') return;

            $request->update(['status' => 'processing', 'started_at' => now()]);

            try {
                $agent  = $request->agent;
                $prompt = $request->prompt ?? $agent->defaultPrompt();
                $driver = $manager->driver($agent->provider);

                $result = $driver->complete(new AiCompletionRequest(
                    model:        $agent->model,
                    systemPrompt: $prompt->system_prompt,
                    userMessage:  $request->rendered_prompt,
                    temperature:  $agent->temperature,
                    maxTokens:    $agent->max_tokens,
                    timeoutSec:   $agent->timeout_seconds,
                ));

                $request->update([
                    'status'        => 'done',
                    'ai_output'     => $result->content,
                    'finish_reason' => $result->finishReason,
                    'input_tokens'  => $result->inputTokens,
                    'output_tokens' => $result->outputTokens,
                    'total_tokens'  => $result->totalTokens,
                    'cost_usd'      => $result->costUsd,
                    'duration_ms'   => $result->durationMs,
                    'completed_at'  => now(),
                ]);

                RecordAiUsageAction::run($request);

            } catch (\Throwable $e) {
                $request->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at'  => now(),
                ]);

                ActivityLogger::error('ai_copilot', 'request_failed', $request, [
                    'agent_slug' => $agent->slug,
                    'error'      => $e->getMessage(),
                ]);

                if ($this->attempts() >= $this->tries) {
                    throw $e; // Đưa vào failed_jobs
                }
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        AiRequest::where('id', $this->aiRequestId)
            ->where('status', 'processing')
            ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
    }
}
```

### 6.3 RecordAiUsageAction

```php
class RecordAiUsageAction
{
    use AsAction;

    public function handle(AiRequest $request): void
    {
        $yearMonth = now()->format('Y-m');
        $orgId     = $request->organization_id;
        $agentId   = $request->agent_id;

        DB::transaction(function () use ($orgId, $agentId, $request, $yearMonth) {
            // Update monthly aggregate (total)
            AiMonthlyUsage::lockForUpdate()->updateOrCreate(
                ['organization_id' => $orgId, 'year_month' => $yearMonth, 'agent_id' => null],
                ['task_type' => null]
            )->increment('total_requests');

            AiMonthlyUsage::where('organization_id', $orgId)
                ->where('year_month', $yearMonth)
                ->whereNull('agent_id')
                ->update([
                    'successful_requests' => DB::raw('successful_requests + 1'),
                    'total_input_tokens'  => DB::raw("total_input_tokens + {$request->input_tokens}"),
                    'total_output_tokens' => DB::raw("total_output_tokens + {$request->output_tokens}"),
                    'total_tokens'        => DB::raw("total_tokens + {$request->total_tokens}"),
                    'total_cost_usd'      => DB::raw("total_cost_usd + {$request->cost_usd}"),
                ]);

            // Update per-agent breakdown
            AiMonthlyUsage::lockForUpdate()->updateOrCreate(
                ['organization_id' => $orgId, 'year_month' => $yearMonth, 'agent_id' => $agentId],
                ['task_type' => $request->agent->task_type]
            );
            // same increments...
        });

        // Sync quota with SubscriptionContext
        $org = TenantContext::get()?->getOrganization()
            ?? Organization::find($orgId);

        RecordFeatureUsageAction::run($org, 'quota.ai_requests', 1);
        RecordFeatureUsageAction::run($org, 'quota.ai_tokens', $request->total_tokens);
    }
}
```

### 6.4 Các Action phụ trợ

```
Actions/
├── ExecuteAiTaskAction.php        ← Entry point (public)
├── RecordAiUsageAction.php        ← Ghi usage sau mỗi request thành công
├── StoreAiAgentAction.php         ← CRUD agent (AI_OP)
├── UpdateAiAgentAction.php
├── DestroyAiAgentAction.php
├── StoreAiPromptAction.php        ← CRUD prompt template
├── UpdateAiPromptAction.php
├── SetDefaultPromptAction.php     ← Đặt prompt làm default
├── RetryAiRequestAction.php       ← Retry failed request
└── CancelAiRequestAction.php      ← Cancel pending request
```

---

## 7. Models

### 7.1 AiAgent

```php
class AiAgent extends TenantAwareModel
{
    protected $table = 'ai_agents';

    protected $fillable = [
        'uuid', 'organization_id', 'name', 'slug', 'description',
        'task_type', 'provider', 'model', 'temperature', 'max_tokens',
        'timeout_seconds', 'sync_mode', 'is_active', 'is_system', 'created_by',
    ];

    protected $casts = [
        'temperature'    => 'float',
        'sync_mode'      => 'boolean',
        'is_active'      => 'boolean',
        'is_system'      => 'boolean',
    ];

    public function prompts(): HasMany
    {
        return $this->hasMany(AiPrompt::class, 'agent_id');
    }

    public function defaultPrompt(): AiPrompt
    {
        return $this->prompts()
            ->where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(AiRequest::class, 'agent_id');
    }

    // Override global scope cho system agents (organization_id = null)
    protected static function booted(): void
    {
        static::addGlobalScope('active', fn ($q) => $q->where('is_active', true));
    }

    public static function findBySlug(string $slug, ?int $orgId): self
    {
        return static::withoutGlobalScope('active')
            ->where('slug', $slug)
            ->where(fn ($q) => $q->where('organization_id', $orgId)->orWhereNull('organization_id'))
            ->orderByRaw('organization_id IS NULL ASC') // org override wins
            ->firstOrFail();
    }
}
```

### 7.2 AiRequest

```php
class AiRequest extends Model // KHÔNG extend TenantAwareModel — immutable, không softDelete
{
    protected $table = 'ai_requests';
    public $timestamps = true;

    // Guard mutability
    public function save(array $options = []): bool
    {
        // Allow saves only on status transitions
        if ($this->exists && in_array($this->getOriginal('status'), ['done', 'failed'])) {
            throw new \RuntimeException('AiRequest is immutable after completion.');
        }
        return parent::save($options);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AiAgent::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeCurrentMonth(Builder $q): Builder
    {
        return $q->where('created_at', '>=', now()->startOfMonth());
    }

    public function scopeForOrg(Builder $q, int $orgId): Builder
    {
        return $q->where('organization_id', $orgId);
    }

    public function scopeDone(Builder $q): Builder
    {
        return $q->where('status', 'done');
    }

    // Computed
    public function isPending(): bool { return $this->status === 'pending'; }
    public function isDone(): bool    { return $this->status === 'done'; }
    public function isFailed(): bool  { return $this->status === 'failed'; }

    public function costFormatted(): string
    {
        return '$' . number_format($this->cost_usd, 4);
    }
}
```

### 7.3 AiMonthlyUsage

```php
class AiMonthlyUsage extends Model
{
    protected $table    = 'ai_monthly_usages';
    public $timestamps  = false; // chỉ có updated_at managed by DB

    public function scopeCurrentMonth(Builder $q, int $orgId): Builder
    {
        return $q->where('organization_id', $orgId)
                 ->where('year_month', now()->format('Y-m'));
    }

    public function scopeAggregate(Builder $q): Builder
    {
        return $q->whereNull('agent_id');
    }
}
```

---

## 8. HTTP Layer

### 8.1 Routes

```php
// Modules/AiCopilot/routes/web.php
Route::middleware(['auth', 'tenant'])->prefix('dashboard/ai')->name('ai_copilot.')->group(function () {

    // ── Task execution (end-user facing) ─────────────────────────────
    Route::post('execute',              [AiTaskController::class, 'execute'])   ->name('execute');
    Route::get('requests/{uuid}',       [AiTaskController::class, 'poll'])      ->name('requests.poll');

    // ── Usage dashboard ───────────────────────────────────────────────
    Route::get('usage',                 [AiUsageController::class, 'index'])    ->name('usage.index');

    // ── Prompt Library (AI_OP) ────────────────────────────────────────
    Route::middleware('can:prompt.full')->prefix('prompts')->name('prompts.')->group(function () {
        Route::get('/',                 [AiPromptController::class, 'index'])   ->name('index');
        Route::get('/create',           [AiPromptController::class, 'create'])  ->name('create');
        Route::post('/',                [AiPromptController::class, 'store'])   ->name('store');
        Route::get('/{prompt}/edit',    [AiPromptController::class, 'edit'])    ->name('edit');
        Route::put('/{prompt}',         [AiPromptController::class, 'update'])  ->name('update');
        Route::post('/{prompt}/default',[AiPromptController::class, 'setDefault'])->name('setDefault');
        Route::delete('/{prompt}',      [AiPromptController::class, 'destroy']) ->name('destroy');
    });

    // ── Agent Config (AI_OP) ──────────────────────────────────────────
    Route::middleware('can:ai_copilot.config')->prefix('agents')->name('agents.')->group(function () {
        Route::get('/',                 [AiAgentController::class, 'index'])    ->name('index');
        Route::get('/{agent}/edit',     [AiAgentController::class, 'edit'])     ->name('edit');
        Route::put('/{agent}',          [AiAgentController::class, 'update'])   ->name('update');
        Route::post('/',                [AiAgentController::class, 'store'])    ->name('store');
        Route::delete('/{agent}',       [AiAgentController::class, 'destroy'])  ->name('destroy');
    });

    // ── Request Logs (AI_OP, Admin) ───────────────────────────────────
    Route::middleware('can:ai_logs.full')->prefix('logs')->name('logs.')->group(function () {
        Route::get('/',                 [AiRequestLogController::class, 'index'])   ->name('index');
        Route::get('/{request}',        [AiRequestLogController::class, 'show'])    ->name('show');
        Route::post('/{request}/retry', [AiRequestLogController::class, 'retry'])   ->name('retry');
    });
});
```

### 8.2 AiTaskController

```php
class AiTaskController extends Controller
{
    // POST /dashboard/ai/execute
    public function execute(AiTaskRequest $request, ExecuteAiTaskAction $action): JsonResponse
    {
        $this->authorize('ai_copilot.use');

        $data      = AiTaskData::validateAndCreate($request->all());
        $aiRequest = $action->handle(
            agentSlug: $data->agent_slug,
            variables: $data->variables,
            subject:   $this->resolveSubject($data->subject_type, $data->subject_id),
        );

        return response()->json([
            'uuid'   => $aiRequest->uuid,
            'status' => $aiRequest->status,
            'output' => $aiRequest->isDone() ? $aiRequest->ai_output : null,
        ], 202);
    }

    // GET /dashboard/ai/requests/{uuid}  — polling endpoint
    public function poll(string $uuid): JsonResponse
    {
        $request = AiRequest::where('uuid', $uuid)
            ->where('organization_id', TenantContext::getOrganizationId())
            ->firstOrFail();

        return response()->json([
            'uuid'         => $request->uuid,
            'status'       => $request->status,
            'output'       => $request->ai_output,
            'total_tokens' => $request->total_tokens,
            'duration_ms'  => $request->duration_ms,
            'error'        => $request->error_message,
        ]);
    }
}
```

### 8.3 Request Data (Validation)

```php
// Modules/AiCopilot/app/Data/Requests/AiTaskData.php
class AiTaskData extends Data
{
    public function __construct(
        #[Required, StringType, Max(80)]   public readonly string $agent_slug,
        #[Required, ArrayType]             public readonly array  $variables,
        #[Nullable, StringType, Max(150)]  public readonly ?string $subject_type = null,
        #[Nullable, IntegerType]           public readonly ?int    $subject_id   = null,
    ) {}

    public static function rules(): array
    {
        return [
            'agent_slug'  => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_.]+$/'],
            'variables'   => ['required', 'array', 'max:20'],
            'variables.*' => ['nullable', 'string', 'max:5000'],
            'subject_type'=> ['nullable', 'string', 'max:150'],
            'subject_id'  => ['nullable', 'integer', 'min:1'],
        ];
    }
}
```

---

## 9. RBAC & Permissions

### 9.1 Permissions mới (thêm vào PermissionEnum)

```php
// Thêm vào app/Enums/PermissionEnum.php
// ══ AI COPILOT ════════════════════════════════════════════════════
// CEO=View Usage | Ops=View Usage | AI_OP=Config+Logs | Admin=Full
case AI_COPILOT_USE          = 'ai_copilot.use';          // Trigger AI tasks
case AI_COPILOT_CONFIG       = 'ai_copilot.config';       // Config agents (AI_OP)
case AI_COPILOT_VIEW_USAGE   = 'ai_copilot.view_usage';   // View usage dashboard
```

### 9.2 Role mapping (thêm vào config/permissions.php)

```php
R::CEO->value => [
    P::AI_COPILOT_USE->value,
    P::AI_COPILOT_VIEW_USAGE->value,
    // prompt.view đã có
],

R::SALES->value => [
    P::AI_COPILOT_USE->value,   // Dùng email.draft, lead.score_analysis
],

R::OPS->value => [
    P::AI_COPILOT_USE->value,
    P::AI_COPILOT_VIEW_USAGE->value,
],

R::HR->value => [
    P::AI_COPILOT_USE->value,   // hr.feedback_draft, hr.job_description
],

R::MARKETING->value => [
    P::AI_COPILOT_USE->value,
],

R::AI_OP->value => [
    P::AI_COPILOT_USE->value,
    P::AI_COPILOT_CONFIG->value,
    P::AI_COPILOT_VIEW_USAGE->value,
    // ai_logs.full đã có → xem AiRequest logs
    // prompt.full đã có → quản lý prompts
],

R::ADMIN->value => [
    P::AI_COPILOT_USE->value,
    P::AI_COPILOT_CONFIG->value,
    P::AI_COPILOT_VIEW_USAGE->value,
    // ai_logs.full đã có
    // prompt.admin_config đã có
],
```

### 9.3 Policy

```php
// AiAgentPolicy — chỉ cần cho config, dùng Permission thay Policy cho simplicity
// AiPrompt → dùng permission prompt.full (đã có)
// AiRequest execute → permission ai_copilot.use
```

---

## 10. Frontend Integration

### 10.1 Alpine.js Component — AI Task Button

Pattern tái sử dụng được nhúng vào bất kỳ view nào:

```html
{{-- Ví dụ trong SOP step edit view --}}
<div x-data="aiTask({
    agentSlug: 'sop.step_draft',
    variables: { step_title: '{{ $step->title }}', department: '{{ $step->department }}' },
    subjectType: 'Modules\\Sop\\Models\\SopStep',
    subjectId: {{ $step->id }},
    onDone: (output) => $dispatch('ai-output', { text: output })
})">
    <button @click="run()" :disabled="loading" class="btn btn-sm btn-outline btn-primary">
        <span x-show="!loading">✨ Gợi ý AI</span>
        <span x-show="loading" class="loading loading-spinner loading-xs"></span>
    </button>
    <p x-show="error" x-text="error" class="text-error text-xs mt-1"></p>
</div>
```

```js
// resources/js/components/aiTask.js
function aiTask({ agentSlug, variables, subjectType, subjectId, onDone }) {
    return {
        loading: false,
        error: null,
        pollInterval: null,

        async run() {
            this.loading = true;
            this.error = null;

            try {
                const res = await fetch('/dashboard/ai/execute', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ agent_slug: agentSlug, variables, subject_type: subjectType, subject_id: subjectId }),
                });

                if (res.status === 402) {
                    this.error = 'Đã hết quota AI tháng này. Vui lòng nâng cấp gói.';
                    return;
                }

                const data = await res.json();

                if (data.status === 'done') {
                    onDone(data.output);
                } else {
                    this.pollForResult(data.uuid, onDone);
                }
            } catch (e) {
                this.error = 'Lỗi kết nối. Vui lòng thử lại.';
            } finally {
                if (!this.pollInterval) this.loading = false;
            }
        },

        pollForResult(uuid, onDone) {
            let attempts = 0;
            this.pollInterval = setInterval(async () => {
                attempts++;
                if (attempts > 30) { // 30s timeout
                    clearInterval(this.pollInterval);
                    this.loading = false;
                    this.error = 'AI mất quá nhiều thời gian. Vui lòng thử lại.';
                    return;
                }

                const res  = await fetch(`/dashboard/ai/requests/${uuid}`);
                const data = await res.json();

                if (data.status === 'done') {
                    clearInterval(this.pollInterval);
                    this.loading = false;
                    onDone(data.output);
                } else if (data.status === 'failed') {
                    clearInterval(this.pollInterval);
                    this.loading = false;
                    this.error = 'AI xử lý thất bại. Thử lại hoặc liên hệ admin.';
                }
            }, 1000); // poll mỗi 1s
        },
    };
}
```

### 10.2 Usage Dashboard (AI_OP / CEO)

**Route**: `GET /dashboard/ai/usage`

**View sections**:
1. **Quota bar** — `quota.ai_requests` current / limit (reuse `quota-bar` partial)
2. **Monthly stats** — requests, tokens, cost (line chart qua 6 tháng)
3. **Top agents** — breakdown per agent slug (table)
4. **Recent requests** — last 20 requests với status, duration, cost

---

## 11. Config

```php
// Modules/AiCopilot/config/ai_copilot.php
return [
    'providers' => [
        'claude' => [
            'api_key'     => env('ANTHROPIC_API_KEY'),
            'default_model' => 'claude-haiku-4-5-20251001',
        ],
        'openai' => [
            'api_key'     => env('OPENAI_API_KEY'),
            'default_model' => 'gpt-4o-mini',
        ],
    ],

    'queue' => [
        'connection' => env('AI_QUEUE_CONNECTION', 'redis'),
        'name'       => 'ai',
        'retry_after' => 90,
    ],

    'defaults' => [
        'temperature'     => 0.7,
        'max_tokens'      => 1024,
        'timeout_seconds' => 30,
    ],

    'cost_alert_usd' => env('AI_COST_ALERT_USD', 50.0), // Alert khi cost/month > $50
];
```

**.env additions**:
```
ANTHROPIC_API_KEY=sk-ant-...
OPENAI_API_KEY=sk-...
AI_QUEUE_CONNECTION=redis
```

---

## 12. ActivityLog Integration

```php
// Các events cần log
ActivityLogger::info('ai_copilot', 'task_executed', $aiRequest, [
    'agent_slug'   => $agent->slug,
    'subject_type' => $aiRequest->subject_type,
    'subject_id'   => $aiRequest->subject_id,
    'total_tokens' => $aiRequest->total_tokens,
]);

ActivityLogger::warning('ai_copilot', 'quota_approaching', null, [
    'current'  => $monthRequests,
    'limit'    => org_limit('quota.ai_requests'),
    'pct'      => round($monthRequests / org_limit('quota.ai_requests') * 100),
]);

ActivityLogger::error('ai_copilot', 'request_failed', $aiRequest, [
    'agent_slug' => $agent->slug,
    'provider'   => $aiRequest->provider,
    'error'      => $aiRequest->error_message,
]);

ActivityLogger::info('ai_copilot', 'prompt_updated', $prompt, [
    'agent_slug' => $agent->slug,
    'version'    => $prompt->version,
]);
```

---

## 13. Module Structure

```
Modules/AiCopilot/
├── app/
│   ├── Actions/
│   │   ├── ExecuteAiTaskAction.php       ← Entry point chính
│   │   ├── ProcessAiRequestJob.php       ← Queue job (trong Actions vì AsAction)
│   │   ├── RecordAiUsageAction.php
│   │   ├── RetryAiRequestAction.php
│   │   ├── StoreAiAgentAction.php
│   │   ├── UpdateAiAgentAction.php
│   │   ├── DestroyAiAgentAction.php
│   │   ├── StoreAiPromptAction.php
│   │   ├── UpdateAiPromptAction.php
│   │   └── SetDefaultPromptAction.php
│   ├── Data/
│   │   └── Requests/
│   │       ├── AiTaskData.php
│   │       ├── StoreAiAgentData.php
│   │       └── StoreAiPromptData.php
│   ├── Drivers/
│   │   ├── Contracts/
│   │   │   └── AiDriverContract.php
│   │   ├── DTOs/
│   │   │   ├── AiCompletionRequest.php
│   │   │   └── AiCompletionResult.php
│   │   ├── ClaudeDriver.php
│   │   ├── OpenAiDriver.php
│   │   └── MockDriver.php
│   ├── Exceptions/
│   │   ├── FeatureNotAvailableException.php
│   │   └── QuotaExceededException.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AiTaskController.php
│   │       ├── AiUsageController.php
│   │       ├── AiAgentController.php
│   │       ├── AiPromptController.php
│   │       └── AiRequestLogController.php
│   ├── Jobs/
│   │   └── ProcessAiRequestJob.php
│   ├── Models/
│   │   ├── AiAgent.php
│   │   ├── AiPrompt.php
│   │   ├── AiRequest.php
│   │   └── AiMonthlyUsage.php
│   ├── Queries/
│   │   ├── ListAiAgentsQuery.php
│   │   ├── ListAiRequestsQuery.php
│   │   └── GetUsageSummaryQuery.php
│   ├── Services/
│   │   └── AiDriverManager.php
│   └── Providers/
│       ├── AiCopilotServiceProvider.php
│       └── RouteServiceProvider.php
├── config/
│   └── ai_copilot.php
├── database/
│   ├── migrations/
│   │   ├── 000_create_ai_agents_table.php
│   │   ├── 001_create_ai_prompts_table.php
│   │   ├── 002_create_ai_requests_table.php
│   │   └── 003_create_ai_monthly_usages_table.php
│   └── seeders/
│       ├── AiCopilotDatabaseSeeder.php
│       ├── SystemAgentsSeeder.php        ← 9 system agents
│       └── SystemPromptsSeeder.php       ← Default prompts per agent
├── resources/
│   └── views/
│       ├── usage/
│       │   └── index.blade.php
│       ├── agents/
│       │   ├── index.blade.php
│       │   └── edit.blade.php
│       ├── prompts/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       └── logs/
│           ├── index.blade.php
│           └── show.blade.php
├── routes/
│   └── web.php
├── module.json
└── composer.json
```

---

## 14. Implementation Phases

### Phase 1 — Core Engine (tuần 1)

- [ ] Migrations (4 tables)
- [ ] Models: AiAgent, AiPrompt, AiRequest, AiMonthlyUsage
- [ ] Driver abstraction: Contract + ClaudeDriver + MockDriver
- [ ] AiDriverManager service
- [ ] ExecuteAiTaskAction (sync mode first)
- [ ] ProcessAiRequestJob (queue)
- [ ] RecordAiUsageAction
- [ ] Exceptions: FeatureNotAvailableException, QuotaExceededException
- [ ] ServiceProvider + config

### Phase 2 — Quota & Subscription wiring (tuần 1-2)

- [ ] Thêm `quota.ai_tokens` và `limit.ai_agents` vào Subscription config
- [ ] Gate checks trong ExecuteAiTaskAction
- [ ] Seeder: 9 system agents + default prompts
- [ ] AiTaskController (execute + poll endpoints)
- [ ] Thêm permissions vào PermissionEnum + config/permissions.php
- [ ] `php artisan permissions:sync`

### Phase 3 — Prompt & Agent Management UI (tuần 2)

- [ ] AiPromptController CRUD
- [ ] AiAgentController CRUD (override system agents per org)
- [ ] Blade views: agents/index, agents/edit, prompts/index, prompts/create, prompts/edit
- [ ] ActivityLogger integration

### Phase 4 — Usage Dashboard (tuần 2-3)

- [ ] GetUsageSummaryQuery
- [ ] AiUsageController
- [ ] Views: usage/index với quota bar + charts
- [ ] AiRequestLogController + views: logs/index, logs/show
- [ ] Retry action cho failed requests

### Phase 5 — Widget Integration (tuần 3)

- [ ] Alpine.js `aiTask()` component
- [ ] Nhúng vào SOP step editor
- [ ] Nhúng vào Employee performance form
- [ ] Nhúng vào KPI analysis section
- [ ] Nhúng vào Lead score panel

---

## 15. Composer & Dependencies

```json
// Modules/AiCopilot/composer.json
{
    "name": "minhan/ai-copilot",
    "require": {
        "anthropic-php/sdk": "^1.0",
        "openai-php/laravel": "^0.10"
    },
    "autoload": {
        "psr-4": {
            "Modules\\AiCopilot\\": "app/",
            "Modules\\AiCopilot\\Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

```bash
composer require anthropic-php/sdk openai-php/laravel
```

---

## 16. Queue Setup

```bash
# Worker riêng cho AI queue (tránh block queue chính)
php artisan queue:work --queue=ai --timeout=90 --tries=2 --sleep=1

# Trong production: supervisor config
[program:minhan-ai-worker]
command=php /var/www/html/minhan/artisan queue:work --queue=ai --timeout=90 --tries=2
numprocs=2
```

---

## 17. Error Handling & Resilience

| Tình huống | Xử lý |
|---|---|
| API key sai / provider down | Catch → `status=failed`, log error, không retry |
| Timeout (>30s) | Job timeout → `failed()` hook → `status=failed` |
| Rate limit 429 từ provider | Retry với exponential backoff (2 tries) |
| Quota vượt giới hạn | Exception ngay lập tức, KHÔNG gọi API |
| Token > max_tokens của model | Truncate prompt trước khi gửi |
| Subject không tồn tại | Validate `subject_id` trong Data class |
| Kết quả AI rỗng | `finish_reason=max_tokens` → lưu partial output |

---

## 18. Matching với hệ thống hiện tại

| Điểm tích hợp | Cách match |
|---|---|
| **TenantAwareModel** | AiAgent, AiPrompt, AiMonthlyUsage extend TenantAwareModel |
| **TenantAwareJob** | ProcessAiRequestJob extend TenantAwareJob |
| **SubscriptionContext** | `org_can('module.ai')`, `org_at_limit('quota.ai_requests')` |
| **RecordFeatureUsageAction** | Gọi sau mỗi request thành công |
| **ActivityLogger** | Log execute, fail, prompt_updated |
| **PermissionEnum** | 3 cases mới trong block AI COPILOT |
| **config/permissions.php** | Map roles → permissions như pattern hiện có |
| **AVSA Actions** | Tất cả business logic trong Action classes với `AsAction` |
| **Spatie LaravelData** | AiTaskData, StoreAiAgentData extend Data |
| **RoleEnum::visibleModules()** | Thêm `'ai_copilot'` cho CEO, OPS, AI_OP, ADMIN |
| **Sidebar config** | Thêm entry trong `config/permissions.php` sidebar section |

---

*Spec v1.0.0 — 2026-06-11 · Minhan Platform*
