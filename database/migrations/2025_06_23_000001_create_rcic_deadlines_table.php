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
        if (!Schema::hasTable('rcic_deadlines')) {
            Schema::create('rcic_deadlines', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->date('deadline_date')->nullable();
                $table->integer('days_before')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rcic_deadlines');
    }
};
