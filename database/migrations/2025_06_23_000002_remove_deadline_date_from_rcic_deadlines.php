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
        Schema::table('rcic_deadlines', function (Blueprint $table) {
            $table->dropColumn('deadline_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rcic_deadlines', function (Blueprint $table) {
            $table->date('deadline_date')->nullable();
        });
    }
};
