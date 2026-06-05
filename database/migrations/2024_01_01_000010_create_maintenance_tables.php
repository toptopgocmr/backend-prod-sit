<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->enum('type', ['machine_a_coudre','climatiseur','groupe_electrogene','ordinateur','autre']);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->enum('status', ['operationnel','en_panne','en_maintenance','hors_service'])->default('operationnel');
            $table->string('location')->nullable(); // Atelier A, Bureau, etc.
            $table->string('photo')->nullable();
            $table->integer('maintenance_interval_days')->default(90);
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['preventive','corrective','urgence']);
            $table->enum('status', ['signale','en_cours','resolu','annule'])->default('signale');
            $table->string('title');
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->string('technician_name')->nullable();
            $table->string('technician_phone')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('invoice_photo')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('equipment');
    }
};
