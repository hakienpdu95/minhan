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
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete()->comment('Tổ chức');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete()->comment('Người mời — null nếu user bị xóa');
            $table->string('email', 255)->index()->comment('Email được mời');
            $table->enum('role', ['owner', 'admin', 'manager', 'member'])->default('member')->index()->comment('Vai trò sau khi chấp nhận');
            $table->string('token', 64)->unique()->comment('Token bí mật để chấp nhận lời mời');
            $table->timestamp('accepted_at')->nullable()->comment('Thời điểm chấp nhận lời mời');
            $table->timestamp('expires_at')->nullable()->comment('Thời điểm hết hạn lời mời');
            $table->timestamps();
            
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
    }
};