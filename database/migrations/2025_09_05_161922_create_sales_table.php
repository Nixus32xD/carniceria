<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->enum('payment_method',['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->enum('status',['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
