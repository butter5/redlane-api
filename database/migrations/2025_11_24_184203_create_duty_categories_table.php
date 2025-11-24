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
        Schema::create('duty_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon_name')->nullable();
            
            $table->foreignId('calculation_method_type_id')
                ->constrained('calculation_method_types')
                ->onDelete('restrict');
            
            $table->decimal('duty_rate', 8, 4);
            $table->foreignId('duty_unit_type_id')
                ->nullable()
                ->constrained('unit_types')
                ->onDelete('restrict');
            
            $table->decimal('exemption_quantity', 10, 2)->nullable();
            $table->foreignId('exemption_unit_type_id')
                ->nullable()
                ->constrained('unit_types')
                ->onDelete('restrict');
            
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
            $table->index(['effective_from', 'effective_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duty_categories');
    }
};
