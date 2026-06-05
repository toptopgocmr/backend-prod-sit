<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('measurement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // couturier
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_name')->nullable();
            $table->string('model_photo')->nullable();
            $table->text('model_description')->nullable();
            $table->enum('gender', ['homme','femme','enfant']);
            $table->enum('garment_type', ['robe','costume','pantalon','chemise','boubou','ensemble','autre']);
            // Tissu utilisé
            $table->foreignId('fabric_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('fabric_meters', 6, 2)->nullable();
            $table->string('fabric_color')->nullable();
            // Accessoires
            $table->json('accessories')->nullable(); // [{name, qty, price}]
            // Statuts workflow
            $table->enum('status', [
                'recu','en_decoupe','en_couture','finition','controle_qualite','pret','livre','annule'
            ])->default('recu');
            $table->enum('payment_status', ['unpaid','partial','paid'])->default('unpaid');
            $table->enum('payment_method', ['cash','mobile_money','card','credit'])->nullable();
            // Finances
            $table->decimal('fabric_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('accessories_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0); // Acompte
            // Dates
            $table->date('delivery_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Historique des changements de statut
        Schema::create('custom_order_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('custom_order_statuses');
        Schema::dropIfExists('custom_orders');
    }
};
