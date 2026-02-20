<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop if partially created from failed previous attempt
        Schema::dropIfExists('tier_member_types');

        // Detect the actual type of tiers.id to match it
        $tierIdType = DB::select("SELECT DATA_TYPE, COLUMN_TYPE 
                                  FROM information_schema.COLUMNS 
                                  WHERE TABLE_SCHEMA = DATABASE() 
                                  AND TABLE_NAME = 'tiers' 
                                  AND COLUMN_NAME = 'id'");

        $columnType = strtolower($tierIdType[0]->COLUMN_TYPE ?? 'bigint unsigned');

        Schema::create('tier_member_types', function (Blueprint $table) use ($columnType) {
            $table->id();

            // Match tiers.id type exactly to avoid FK incompatibility
            if (str_contains($columnType, 'unsigned')) {
                $table->unsignedBigInteger('tier_id');
            } else {
                $table->unsignedInteger('tier_id');
            }

            $table->string('membership_type_code');
            $table->boolean('is_active')->default(1)
                  ->comment('0 = soft deleted when API no longer returns this type');
            $table->timestamp('deleted_at')->nullable()
                  ->comment('Timestamp when MemberType was soft deleted from API');
            $table->timestamps();
        });

        // Add FK separately after table is created
        try {
            Schema::table('tier_member_types', function (Blueprint $table) {
                $table->foreign('tier_id')
                      ->references('id')
                      ->on('tiers')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK failed — table still works without it, log the issue
            \Illuminate\Support\Facades\Log::warning('tier_member_types FK skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_member_types');
    }
};


# Only tier_interest_groups
// php artisan migrate --path=database/migrations/2026_02_20_124905_create_tier_interest_groups_table.php
# Only tier_member_types
// php artisan migrate --path=database/migrations/2026_02_20_124954_create_tier_member_types_table.php

