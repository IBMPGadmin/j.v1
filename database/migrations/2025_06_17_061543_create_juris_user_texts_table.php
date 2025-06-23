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
        // Table already created in the previous migration
        if (!Schema::hasTable('juris_user_texts')) {
            Schema::create('juris_user_texts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('document_table', 100);
                $table->integer('document_section_id');
                $table->text('text_content')->nullable();
                $table->enum('text_type', ['note', 'highlight', 'comment'])->default('note');
                $table->timestamps();
                
                $table->index('user_id');
                $table->index(['document_table', 'document_section_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('juris_user_texts');
    }
};
