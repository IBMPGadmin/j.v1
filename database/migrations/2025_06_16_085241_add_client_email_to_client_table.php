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
        // Column is already added in the create_client_table migration
        // Skipping to avoid duplicate column error
        if (!Schema::hasColumn('client_table', 'client_email')) {
            Schema::table('client_table', function (Blueprint $table) {
                $table->string('client_email')->after('client_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_table', function (Blueprint $table) {
            $table->dropColumn('client_email');
        });
    }
};
