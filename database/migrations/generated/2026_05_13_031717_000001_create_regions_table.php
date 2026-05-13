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
        Schema::create('regions', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('UUID primary key');
            $table->unsignedBigInteger('sort_order')->default(0)->index()->comment('Thứ tự sắp xếp — set thủ công khi insert');
            $table->string('name', 255)->index()->comment('Tên vùng');
            $table->timestamps();
            $table->softDeletes();
            
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};