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
        // First check if the table exists
        if (Schema::hasTable('client_table')) {
            // Then check if the column doesn't already exist
            if (!Schema::hasColumn('client_table', 'last_accessed')) {
                Schema::table('client_table', function (Blueprint $table) {
                    $table->timestamp('last_accessed')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('client_table')) {
            if (Schema::hasColumn('client_table', 'last_accessed')) {
                Schema::table('client_table', function (Blueprint $table) {
                    $table->dropColumn('last_accessed');
                });
            }
        }
    }
};
