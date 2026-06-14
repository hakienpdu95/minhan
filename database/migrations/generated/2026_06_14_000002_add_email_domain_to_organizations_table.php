<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'email_domain')) {
                $table->string('email_domain', 100)->nullable()
                    ->comment('VD: company.com — dùng để phát hiện email tổ chức khi HR tạo tài khoản NV');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'email_domain')) {
                $table->dropColumn('email_domain');
            }
        });
    }
};
