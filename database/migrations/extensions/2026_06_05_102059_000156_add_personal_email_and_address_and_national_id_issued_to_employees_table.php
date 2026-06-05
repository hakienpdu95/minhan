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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('personal_email', 150)->nullable();
            $table->text('address')->nullable()->after('personal_email');
            $table->date('national_id_issued')->nullable()->after('address');
            $table->string('bank_account', 30)->nullable()->after('national_id_issued');
            $table->string('bank_name', 100)->nullable()->after('bank_account');
            $table->date('probation_end_date')->nullable()->after('bank_name');
            $table->date('contract_start')->nullable()->after('probation_end_date');
            $table->date('contract_end')->nullable()->after('contract_start');
            $table->decimal('salary_base', 15, 2)->nullable()->after('contract_end');
            $table->char('salary_currency', 3)->default('VND')->after('salary_base');
            $table->string('work_location', 20)->nullable()->after('salary_currency');
            $table->string('emergency_contact_name', 150)->nullable()->after('work_location');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            $table->date('resigned_at')->nullable()->after('emergency_contact_phone');
            $table->text('resignation_reason')->nullable()->after('resigned_at');
            $table->text('notes')->nullable()->after('resignation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['personal_email', 'address', 'national_id_issued', 'bank_account', 'bank_name', 'probation_end_date', 'contract_start', 'contract_end', 'salary_base', 'salary_currency', 'work_location', 'emergency_contact_name', 'emergency_contact_phone', 'resigned_at', 'resignation_reason', 'notes']);
        });
    }
};