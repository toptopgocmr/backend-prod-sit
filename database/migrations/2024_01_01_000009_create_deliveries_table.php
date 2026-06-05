<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('deliverable_type'); // Order ou CustomOrder
            $table->unsignedBigInteger('deliverable_id');
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending','assigned','in_transit','delivered','failed','returned'])->default('pending');
            $table->enum('type', ['livraison','retrait_boutique'])->default('livraison');
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('proof_photo')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['deliverable_type','deliverable_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('deliveries'); }
};
