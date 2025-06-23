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
        // Table already exists, check if we need to add columns
        if (Schema::hasTable('user_pinned_popups')) {
            // Add columns if they don't exist
            Schema::table('user_pinned_popups', function (Blueprint $table) {
                if (!Schema::hasColumn('user_pinned_popups', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                }
                if (!Schema::hasColumn('user_pinned_popups', 'category_id')) {
                    $table->integer('category_id');
                }
                if (!Schema::hasColumn('user_pinned_popups', 'section_id')) {
                    $table->string('section_id', 100);
                }
                if (!Schema::hasColumn('user_pinned_popups', 'section_title')) {
                    $table->string('section_title', 255)->nullable();
                }
                if (!Schema::hasColumn('user_pinned_popups', 'popup_content')) {
                    $table->text('popup_content')->nullable();
                }
                if (!Schema::hasColumn('user_pinned_popups', 'table_name')) {
                    $table->string('table_name', 100)->nullable();
                }
                if (!Schema::hasColumn('user_pinned_popups', 'pinned_at')) {
                    $table->timestamp('pinned_at')->useCurrent();
                }
                if (!Schema::hasColumn('user_pinned_popups', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        } else {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pinned_popups');
    }
};
