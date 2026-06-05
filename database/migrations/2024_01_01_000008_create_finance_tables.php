<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6B7280');
            $table->string('icon')->nullable();
            $table->enum('type', ['achat','salaire','transport','charge','maintenance','autre']);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // qui a saisi
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash','mobile_money','virement','credit'])->default('cash');
            $table->date('expense_date');
            $table->string('receipt_photo')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('payable_type'); // Order ou CustomOrder
            $table->unsignedBigInteger('payable_id');
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash','mobile_money','card','credit']);
            $table->string('transaction_id')->nullable(); // pour mobile money
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payable_type','payable_id']);
        });

        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('paid_by')->constrained('users')->restrictOnDelete();
            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->integer('month');
            $table->integer('year');
            $table->enum('payment_method', ['cash','mobile_money','virement'])->default('cash');
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
