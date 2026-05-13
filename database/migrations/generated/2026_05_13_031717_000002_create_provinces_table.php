<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('UUID primary key');
            $table->unsignedBigInteger('sort_order')->default(0)->index()->comment('Thứ tự sắp xếp — set thủ công khi insert');
            $table->string('name', 255)->index()->comment('Tên tỉnh/thành phố');
            $table->string('short_name', 255)->comment('Tên ngắn gọn của tỉnh/thành phố');
            $table->string('logo', 255)->nullable()->comment('Logo tỉnh');
            $table->char('province_code', 2)->unique()->index()->comment('Mã tỉnh/thành phố');
            $table->enum('place_type', ['thanh-pho', 'tinh'])->default('tinh')->index()->comment('Loại: Thành phố Trung Ương hoặc Tỉnh');
            $table->char('region_id', 36)->comment('Thuộc vùng — FK tới regions.id (uuid char 36)');
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnDelete();
            $table->string('country', 2)->default('VN')->index()->comment('Mã quốc gia');
            $table->boolean('is_active')->default(true)->index()->comment('Trạng thái hoạt động');
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index('region_id');
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};