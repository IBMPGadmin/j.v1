<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create jurisdiction table if it doesn't exist
        if (!Schema::hasTable('jurisdiction')) {
            Schema::create('jurisdiction', function (Blueprint $table) {
                $table->id('jurisdiction_id');
                $table->string('jurisdiction_name');
                $table->string('jurisdiction_type')->nullable(); // Federal, Provincial, Territorial
                $table->timestamps();
            });
            
            // Insert default jurisdictions
            $jurisdictions = [
                ['jurisdiction_id' => 1, 'jurisdiction_name' => 'Federal', 'jurisdiction_type' => 'Federal'],
                ['jurisdiction_id' => 2, 'jurisdiction_name' => 'Alberta', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 3, 'jurisdiction_name' => 'British Columbia', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 4, 'jurisdiction_name' => 'Manitoba', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 5, 'jurisdiction_name' => 'New Brunswick', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 6, 'jurisdiction_name' => 'Newfoundland & Labrador', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 7, 'jurisdiction_name' => 'Nova Scotia', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 8, 'jurisdiction_name' => 'Ontario', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 9, 'jurisdiction_name' => 'Prince Edward Island', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 10, 'jurisdiction_name' => 'Quebec', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 11, 'jurisdiction_name' => 'Saskatchewan', 'jurisdiction_type' => 'Provincial'],
                ['jurisdiction_id' => 12, 'jurisdiction_name' => 'Northwest Territories', 'jurisdiction_type' => 'Territorial'],
                ['jurisdiction_id' => 13, 'jurisdiction_name' => 'Nunavut', 'jurisdiction_type' => 'Territorial'],
                ['jurisdiction_id' => 14, 'jurisdiction_name' => 'Yukon', 'jurisdiction_type' => 'Territorial'],
            ];
            
            DB::table('jurisdiction')->insert($jurisdictions);
        }

        // Create law_subject table if it doesn't exist
        if (!Schema::hasTable('law_subject')) {
            Schema::create('law_subject', function (Blueprint $table) {
                $table->id('law_id');
                $table->string('law_name');
                $table->timestamps();
            });
            
            // Insert default law subjects
            $lawSubjects = [
                ['law_id' => 1, 'law_name' => 'Immigration'],
                ['law_id' => 2, 'law_name' => 'Citizenship'],
                ['law_id' => 3, 'law_name' => 'Criminal'],
            ];
            
            DB::table('law_subject')->insert($lawSubjects);
        }

        // Create acts table if it doesn't exist
        if (!Schema::hasTable('acts')) {
            Schema::create('acts', function (Blueprint $table) {
                $table->id('act_id');
                $table->string('act_name');
                $table->timestamps();
            });
            
            // Insert default acts categories
            $acts = [
                ['act_id' => 1, 'act_name' => 'Acts'],
                ['act_id' => 2, 'act_name' => 'Appeal & Review Processes'],
                ['act_id' => 3, 'act_name' => 'CaseLaw'],
                ['act_id' => 4, 'act_name' => 'Codes'],
                ['act_id' => 5, 'act_name' => 'Enforcement'],
                ['act_id' => 6, 'act_name' => 'Forms'],
                ['act_id' => 7, 'act_name' => 'Guidelines'],
                ['act_id' => 8, 'act_name' => 'Agreements'],
                ['act_id' => 9, 'act_name' => 'Ministerial Instructions'],
                ['act_id' => 10, 'act_name' => 'Operational Bulletins'],
                ['act_id' => 11, 'act_name' => 'Policies'],
                ['act_id' => 12, 'act_name' => 'Procedures'],
                ['act_id' => 13, 'act_name' => 'Regulations'],
            ];
            
            DB::table('acts')->insert($acts);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't drop these tables as they might contain important data
    }
};
