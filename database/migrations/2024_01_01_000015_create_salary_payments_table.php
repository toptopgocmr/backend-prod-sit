<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('paid_by')->constrained('users')->restrictOnDelete();
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('month'); // 1–12
            $table->unsignedSmallInteger('year');
            $table->enum('payment_method', ['cash', 'mobile_money', 'virement'])->default('cash');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
