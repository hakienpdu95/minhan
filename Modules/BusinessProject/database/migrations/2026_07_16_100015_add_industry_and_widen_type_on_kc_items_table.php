<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec Phần 9 (Phase 2 — Knowledge Workspace): mở rộng kc_items với 3 type mới +
        // cột `industry` để "Search theo Industry" (Handbook — tra cứu Case Study cùng ngành ở
        // Discovery dự án sau, khép vòng tri thức). `kc_items.type` là DB enum thật (khác
        // `deliverables.type` cố ý dùng string) — phải ALTER MODIFY để mở rộng danh sách giá trị,
        // không thể chỉ thêm case PHP như DeliverableType.
        DB::statement(
            "ALTER TABLE kc_items MODIFY COLUMN type ENUM(
                'document', 'sop', 'video', 'form', 'faq', 'case_study', 'policy',
                'lessons_learned', 'best_practice', 'industry_knowledge'
            ) NOT NULL"
        );

        Schema::table('kc_items', function (Blueprint $table) {
            $table->string('industry', 100)->nullable()->after('domain_code');
            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::table('kc_items', function (Blueprint $table) {
            $table->dropIndex(['industry']);
            $table->dropColumn('industry');
        });

        // Lưu ý: rollback sẽ lỗi nếu đã có row dùng 3 type mới — chấp nhận được vì down()
        // migration hiếm khi chạy trong thực tế, không cần xử lý phức tạp hơn.
        DB::statement(
            "ALTER TABLE kc_items MODIFY COLUMN type ENUM(
                'document', 'sop', 'video', 'form', 'faq', 'case_study', 'policy'
            ) NOT NULL"
        );
    }
};
