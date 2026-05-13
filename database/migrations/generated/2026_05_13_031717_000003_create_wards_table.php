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
        Schema::create('wards', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('UUID primary key');
            $table->unsignedBigInteger('sort_order')->default(0)->index()->comment('Thứ tự sắp xếp — set thủ công khi insert');
            $table->string('name', 255)->index()->comment('Tên phường/xã');
            $table->char('ward_code', 5)->unique()->index()->comment('Mã phường/xã');
            $table->enum('place_type', ['phuong', 'xa', 'dac-khu'])->default('xa')->index()->comment('Loại: phường, xã, đặc khu');
            $table->char('province_code', 2)->comment('Tỉnh/thành phố liên kết');
            $table->foreign('province_code')->references('province_code')->on('provinces')->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Trạng thái hoạt động');
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index('province_code');
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};