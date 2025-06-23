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
        Schema::create('user_pinned_popups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('category_id');
            $table->string('section_id', 100);
            $table->string('section_title', 255)->nullable();
            $table->text('popup_content')->nullable();
            $table->string('table_name', 100)->nullable();
            $table->timestamp('pinned_at')->useCurrent();
            $table->text('notes')->nullable();
            
            $table->index('user_id');
            $table->index('category_id');
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pinned_popups');
    }
};
