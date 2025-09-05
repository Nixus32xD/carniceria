<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('cut_id')->nullable()->constrained()->onDelete('set null');

            // opcional: eliminar las columnas string antiguas
            $table->dropColumn(['category', 'cut']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('cut')->nullable();

            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('cut_id');
        });
    }
};
