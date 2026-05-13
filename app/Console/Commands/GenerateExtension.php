<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * GenerateExtension v2 — Laravel 13
 *
 * Đọc render_extension_file.json → sinh file ALTER TABLE vào migrations/extensions/
 * Xóa + tạo lại toàn bộ extensions/ mỗi lần chạy (an toàn vì dev dùng migrate:fresh)
 *
 * Format render_extension_file.json:
 * Giống hệt render_migration_file.json, chỉ khác row[0]:
 *   "table_name///action///after_column///comment"
 *   action: add | drop | change
 *   after_column: tên cột đặt sau (__ = không dùng after())
 *
 * Chạy:
 *   php artisan extension:generate
 *   php artisan extension:generate --from=custom_ext.json
 *   php artisan migration:generate --fresh  ← chạy cả 2 generate + fresh
 */
class GenerateExtension extends Command
{
    protected $signature = 'extension:generate
        {--from=render_extension_file.json : JSON file tại project root}
        {--force : Bỏ qua xác nhận}';

    protected $description = 'Generate ALTER TABLE migrations từ JSON vào migrations/extensions/';

    private const DIR_EXTENSIONS = 'extensions';

    private const VALID_TYPES = [
        'increments', 'bigIncrements',
        'tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger',
        'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger',
        'unsignedInteger', 'unsignedBigInteger',
        'uuid', 'ulid',
        'float', 'double', 'decimal',
        'string', 'char', 'binary',
        'text', 'mediumText', 'longText', 'tinyText',
        'date', 'dateTime', 'timestamp', 'time', 'year',
        'boolean', 'enum', 'set', 'json', 'jsonb', 'ip',
    ];

    private const NO_LENGTH_TYPES = [
        'boolean', 'text', 'mediumText', 'longText', 'tinyText',
        'date', 'dateTime', 'timestamp', 'time', 'year',
        'json', 'jsonb', 'ip', 'uuid', 'ulid',
        'unsignedBigInteger', 'unsignedInteger', 'unsignedSmallInteger',
        'unsignedTinyInteger', 'bigInteger', 'integer',
    ];

    // ──────────────────────────────────────────────────────────────

    public function handle(): int
    {
        // 1. Đọc JSON
        $jsonPath = base_path($this->option('from'));

        if (!File::exists($jsonPath)) {
            $this->warn("File không tồn tại: $jsonPath — bỏ qua extension generation.");
            return self::SUCCESS; // Không phải lỗi — file có thể chưa có
        }

        $json = json_decode(File::get($jsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('JSON không hợp lệ: ' . json_last_error_msg());
            return self::FAILURE;
        }

        if (empty($json)) {
            $this->warn('render_extension_file.json rỗng — không có gì để generate.');
            return self::SUCCESS;
        }

        // 2. Validate
        if (!$this->validateSchema($json)) {
            return self::FAILURE;
        }

        // 3. Xóa + tạo lại extensions/
        $extensionsPath = database_path('migrations/' . self::DIR_EXTENSIONS);
        $deleted        = $this->cleanDir($extensionsPath);
        $this->line("<fg=yellow>Đã xóa $deleted file cũ trong extensions/</>");

        // 4. Load template ALTER
        $templatePath = database_path('templates/alter_table.php');
        $template     = File::exists($templatePath)
            ? File::get($templatePath)
            : $this->defaultTemplate();

        // 5. Generate
        $timestamp = Carbon::now();
        $count     = 0;

        foreach ($json as $index => $blockData) {
            $count++;
            $header      = explode('///', array_shift($blockData));
            $tableName   = trim($header[0]);
            $action      = strtolower(trim($header[1] ?? 'add'));
            $afterColumn = trim($header[2] ?? '__'); // __ = không dùng after()
            $blockDesc   = trim($header[3] ?? '');

            // Class name ví dụ: AddDeptAndOrgIdToUsersTable
            $colNames  = $this->extractColumnNames($blockData, $action);
            $colSlug   = Str::studly(implode('_and_', array_slice($colNames, 0, 3)));
            $className = Str::studly($action) . $colSlug . 'To' . Str::studly($tableName) . 'Table';

            // Sinh up() body
            [$upLines, $downLines] = $this->buildBody($action, $tableName, $blockData, $afterColumn);

            $fileName = sprintf(
                '%s_%06d_%s_%s_to_%s_table.php',
                $timestamp->format('Y_m_d_His'),
                $count,
                $action,
                Str::snake(implode('_and_', array_slice($colNames, 0, 3))),
                $tableName
            );

            $content = strtr($template, [
                '__CLASS_NAME__' => $className,
                '__TABLE_NAME__' => $tableName,
                '__ACTION__'     => strtoupper($action),
                '__COMMENT__'    => $blockDesc,
                '__UP_BODY__'    => implode("\n            ", $upLines),
                '__DOWN_BODY__'  => implode("\n            ", $downLines),
            ]);

            File::put("$extensionsPath/$fileName", $content);
            $this->line("<fg=green>  ✓ extensions/$fileName</>");
        }

        $this->info("\nĐã tạo $count extension migration(s).");
        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────
    // BUILD BODY — sinh up() và down()
    // ──────────────────────────────────────────────────────────────

    /**
     * @return array{string[], string[]}  [upLines, downLines]
     */
    private function buildBody(
        string $action,
        string $tableName,
        array  $rows,
        string $firstAfter
    ): array {
        return match ($action) {
            'add'    => $this->buildAddBody($rows, $firstAfter),
            'drop'   => $this->buildDropBody($rows),
            'change' => $this->buildChangeBody($rows),
            default  => [['// TODO'], ['// TODO']],
        };
    }

    // ── ADD ────────────────────────────────────────────────────────

    private function buildAddBody(array $rows, string $firstAfter): array
    {
        $upLines   = [];
        $downLines = [];
        $fkCols    = []; // cột nào là FK → cần dropForeign trước khi drop cột

        $prevColName = $firstAfter !== '__' ? $firstAfter : null;

        foreach ($rows as $row) {
            $p = explode('///', $row);
            while (count($p) < 7) $p[] = '__';
            [$colName, $colType, $colLen, $colNull, $colDefault, $colMod, $colComment] = $p;

            // after() tự động:
            // - cột đầu tiên: after = firstAfter (từ header)
            // - cột tiếp theo: after = cột ngay trước
            $afterClause = $prevColName ? "->after('$prevColName')" : '';
            $prevColName = $colName;

            // Build column definition
            $def = $this->buildColumnDef(
                $colName, $colType, $colLen,
                $colNull, $colDefault, $colMod, $colComment,
                $afterClause
            );

            $upLines[] = $def;

            // Track FK để down() xử lý đúng
            if ($this->isForeignKey($colType, $colMod)) {
                $fkCols[] = $colName;
            }

            $downLines[] = "// down của '$colName' — xem cuối hàm";
        }

        // Down: xóa FK trước, xóa cột sau
        $downLines = [];
        foreach ($fkCols as $fkCol) {
            $downLines[] = "\$table->dropForeign(['$fkCol']);";
        }
        $allNames = array_map(
            fn($r) => "'" . trim(explode('///', $r)[0]) . "'",
            $rows
        );
        if (count($allNames) === 1) {
            $downLines[] = "\$table->dropColumn({$allNames[0]});";
        } else {
            $downLines[] = "\$table->dropColumn([" . implode(', ', $allNames) . "]);";
        }

        return [$upLines, $downLines];
    }

    // ── DROP ───────────────────────────────────────────────────────

    private function buildDropBody(array $rows): array
    {
        $upLines   = [];
        $downLines = [];
        $fkCols    = [];
        $allNames  = [];

        foreach ($rows as $row) {
            $p = explode('///', $row);
            while (count($p) < 7) $p[] = '__';
            [$colName, $colType, , , , $colMod] = $p;

            $allNames[] = "'$colName'";
            if ($this->isForeignKey($colType, $colMod)) {
                $fkCols[] = $colName;
            }
        }

        // Up: drop FK trước, drop cột sau
        foreach ($fkCols as $fk) {
            $upLines[] = "\$table->dropForeign(['$fk']);";
        }
        $upLines[] = count($allNames) === 1
            ? "\$table->dropColumn({$allNames[0]});"
            : "\$table->dropColumn([" . implode(', ', $allNames) . "]);";

        // Down: add lại (skeleton — cần điền type)
        foreach ($rows as $row) {
            $p        = explode('///', $row);
            $colName  = trim($p[0]);
            $colType  = trim($p[1] ?? 'string');
            $downLines[] = "// TODO: \$table->$colType('$colName')->...; // add lại '$colName'";
        }

        return [$upLines, $downLines];
    }

    // ── CHANGE ─────────────────────────────────────────────────────

    private function buildChangeBody(array $rows): array
    {
        $upLines   = [];
        $downLines = [];

        foreach ($rows as $row) {
            $p = explode('///', $row);
            while (count($p) < 7) $p[] = '__';
            [$colName, $colType, $colLen, $colNull, $colDefault, $colMod, $colComment] = $p;

            $def = $this->buildColumnDef(
                $colName, $colType, $colLen,
                $colNull, $colDefault, $colMod, $colComment,
                '' // không dùng after() khi change
            );

            // Thêm ->change() trước dấu ;
            $upLines[]   = rtrim($def, ';') . '->change();';
            $downLines[] = "// TODO: rollback change cho '$colName'";
        }

        return [$upLines, $downLines];
    }

    // ──────────────────────────────────────────────────────────────
    // BUILD COLUMN DEF (tái sử dụng từ GenerateMigration)
    // ──────────────────────────────────────────────────────────────

    private function buildColumnDef(
        string $colName, string $colType,
        string $colLen,  string $colNull,
        string $colDefault, string $colMod, string $colComment,
        string $afterClause
    ): string {
        $nullable = $colNull === '_NULL' || $colNull === 'NULL';
        $noLen    = in_array($colType, self::NO_LENGTH_TYPES);

        // foreignId shorthand
        if ($colType === 'unsignedBigInteger'
            && $colMod !== '__'
            && str_contains($colMod, 'constrained')
            && !str_contains($colMod, 'references(')
        ) {
            $def = "\$table->foreignId('$colName')";
            if ($nullable)       $def .= '->nullable()';
            $def .= $this->normalizeOnDelete($colMod);
            $def .= $afterClause;
            if ($colComment !== '__') $def .= "->comment('" . addslashes($colComment) . "')";
            return $def . ';';
        }

        // Custom FK: char/string + references()
        if (in_array($colType, ['char', 'string', 'uuid'])
            && $colMod !== '__'
            && str_contains($colMod, 'references(')
        ) {
            preg_match("/->references\('([^']+)'\)->constrained\('([^']+)'\)(.*)/", $colMod, $m);
            if (isset($m[1], $m[2])) {
                $params = (!$noLen && $colLen !== '__') ? ', ' . $this->formatParams($colType, $colLen) : '';
                $col    = "\$table->$colType('$colName'$params)";
                if ($nullable)          $col .= '->nullable()';
                if ($colDefault !== '__') $col .= $this->formatDefault($colType, $colDefault);
                $col .= $afterClause;
                if ($colComment !== '__') $col .= "->comment('" . addslashes($colComment) . "')";
                $col .= ';';
                $extra  = $this->normalizeOnDelete(trim($m[3] ?? ''));
                $fk     = "\$table->foreign('$colName')->references('{$m[1]}')->on('{$m[2]}')$extra;";
                return $col . "\n            " . $fk;
            }
        }

        // Normal column
        $params = '';
        if (!$noLen && $colLen !== '__' && $colLen !== '') {
            $params = $this->formatParams($colType, $colLen);
        }

        $def = in_array($colType, ['enum', 'set'])
            ? "\$table->$colType('$colName', $params)"
            : ($params !== ''
                ? "\$table->$colType('$colName', $params)"
                : "\$table->$colType('$colName')");

        if ($nullable)            $def .= '->nullable()';
        if ($colDefault !== '__') $def .= $this->formatDefault($colType, $colDefault);

        // Modifiers không liên quan FK
        if ($colMod !== '__'
            && !str_contains($colMod, 'constrained')
            && !str_contains($colMod, 'references(')
        ) {
            $def .= $colMod;
        }

        $def .= $afterClause;

        if ($colComment !== '__') {
            $def .= "->comment('" . addslashes($colComment) . "')";
        }

        return $def . ';';
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    private function isForeignKey(string $type, string $mod): bool
    {
        return $type === 'unsignedBigInteger' && str_contains($mod, 'constrained')
            || in_array($type, ['char', 'string']) && str_contains($mod, 'references(');
    }

    private function extractColumnNames(array $rows, string $action): array
    {
        return array_map(fn($r) => trim(explode('///', $r)[0]), $rows);
    }

    private function normalizeOnDelete(string $str): string
    {
        return str_replace(
            ["->onDelete('cascade')", "->onDelete('set null')", "->onDelete('restrict')", "->onDelete('no action')"],
            ['->cascadeOnDelete()',   '->nullOnDelete()',       '->restrictOnDelete()',   '->noActionOnDelete()'],
            $str
        );
    }

    private function formatParams(string $type, string $raw): string
    {
        $raw = trim(str_replace(['(', ')'], '', $raw));
        if (in_array($type, ['enum', 'set']) && preg_match('/^\[(.+)\]$/', $raw, $m)) {
            return '[' . implode(', ', array_map(fn($v) => "'" . trim($v) . "'", explode(',', $m[1]))) . ']';
        }
        if ($type === 'decimal' && str_contains($raw, ',')) {
            [$p, $s] = explode(',', $raw, 2);
            return trim($p) . ', ' . trim($s);
        }
        return is_numeric($raw) ? $raw : "'$raw'";
    }

    private function formatDefault(string $type, string $value): string
    {
        if (strtolower($value) === 'null') return '';
        if (in_array(strtolower($value), ['true', 'false'])) return '->default(' . strtolower($value) . ')';
        if (is_numeric($value)) return "->default($value)";
        if (str_starts_with($value, "'") && str_ends_with($value, "'")) return "->default($value)";
        return "->default('$value')";
    }

    private function cleanDir(string $path): int
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
            File::put("$path/.gitkeep", '');
            return 0;
        }
        $count = 0;
        foreach (File::files($path) as $file) {
            if ($file->getFilename() === '.gitkeep') continue;
            File::delete($file->getPathname());
            $count++;
        }
        return $count;
    }

    // ──────────────────────────────────────────────────────────────
    // VALIDATE
    // ──────────────────────────────────────────────────────────────

    private function validateSchema(array $json): bool
    {
        $validActions = ['add', 'drop', 'change'];

        foreach ($json as $i => $block) {
            if (!is_array($block) || empty($block)) {
                $this->error("Block[$i]: rỗng."); return false;
            }
            $header = explode('///', $block[0]);
            $table  = trim($header[0] ?? '');
            $action = strtolower(trim($header[1] ?? ''));

            if (!$table) {
                $this->error("Block[$i]: thiếu tên bảng."); return false;
            }
            if (!in_array($action, $validActions)) {
                $this->error("Block[$i] ($table): action '$action' không hợp lệ. Dùng: " . implode(', ', $validActions));
                return false;
            }

            foreach (array_slice($block, 1) as $j => $field) {
                $p = explode('///', $field);
                if (count($p) < 7) {
                    $this->error("$table[$j]: cần 7 phần, nhận " . count($p) . " → '$field'");
                    return false;
                }
                // drop action: không cần validate type
                if ($action === 'drop') continue;

                if (!in_array($p[1], self::VALID_TYPES)) {
                    $this->error("$table.{$p[0]}: type không hợp lệ '{$p[1]}'");
                    return false;
                }
            }
        }
        return true;
    }

    // ──────────────────────────────────────────────────────────────
    // DEFAULT TEMPLATE (dùng khi không có file templates/alter_table.php)
    // ──────────────────────────────────────────────────────────────

    private function defaultTemplate(): string
    {
        return <<<'PHP'
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        /**
         * __ACTION__: __TABLE_NAME__
         * __COMMENT__
         */
        return new class extends Migration {
            public function up(): void
            {
                Schema::table('__TABLE_NAME__', function (Blueprint $table) {
                    __UP_BODY__
                });
            }

            public function down(): void
            {
                Schema::table('__TABLE_NAME__', function (Blueprint $table) {
                    __DOWN_BODY__
                });
            }
        };
        PHP;
    }
}