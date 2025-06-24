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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique()->after('id');
            $table->string('payment_status')->default('pending')->after('status');
            
            // Rename package_id to subscription_package_id if it exists
            if (Schema::hasColumn('user_subscriptions', 'package_id')) {
                $table->renameColumn('package_id', 'subscription_package_id');
            }
            
            // Add starts_at and expires_at for simplicity
            if (!Schema::hasColumn('user_subscriptions', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('subscription_package_id');
            }
            
            if (!Schema::hasColumn('user_subscriptions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('starts_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('reference');
            $table->dropColumn('payment_status');
            
            // Rename subscription_package_id back to package_id
            if (Schema::hasColumn('user_subscriptions', 'subscription_package_id')) {
                $table->renameColumn('subscription_package_id', 'package_id');
            }
            
            // Drop the columns if they exist
            if (Schema::hasColumn('user_subscriptions', 'starts_at')) {
                $table->dropColumn('starts_at');
            }
            
            if (Schema::hasColumn('user_subscriptions', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
