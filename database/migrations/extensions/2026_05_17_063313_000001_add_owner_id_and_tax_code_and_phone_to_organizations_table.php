<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->index()->after('settings')->comment('ID người sở hữu — không có FK ở DB');
            $table->string('tax_code', 20)->nullable()->after('owner_id')->comment('Mã số thuế doanh nghiệp');
            $table->string('phone', 20)->nullable()->after('tax_code')->comment('Số điện thoại liên hệ');
            $table->string('email', 255)->nullable()->after('phone')->comment('Email liên hệ của tổ chức');
            $table->string('website', 255)->nullable()->after('email')->comment('Website của tổ chức');
            $table->string('industry', 100)->nullable()->index()->after('website')->comment('Ngành nghề kinh doanh');
            $table->string('address', 500)->nullable()->after('industry')->comment('Địa chỉ đầy đủ');
            $table->string('city', 100)->nullable()->after('address')->comment('Thành phố');
            $table->string('country', 2)->default('VN')->index()->after('city')->comment('Mã quốc gia ISO 3166-1');
            $table->string('postal_code', 20)->nullable()->after('country')->comment('Mã bưu chính');
            $table->text('description')->nullable()->after('postal_code')->comment('Mô tả về tổ chức');
            $table->string('logo_path', 500)->nullable()->after('description')->comment('Đường dẫn đến file logo');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['owner_id', 'tax_code', 'phone', 'email', 'website', 'industry', 'address', 'city', 'country', 'postal_code', 'description', 'logo_path']);
        });
    }
};