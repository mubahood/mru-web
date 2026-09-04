<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The published almanac carries three things the table could not hold: the
 * office answerable for each activity (a primary column in the Academic
 * Registrar's document), a category so a year of ~200 entries can be
 * filtered and colour-coded rather than read as one undifferentiated list,
 * and a flag marking the handful of dates worth surfacing on the homepage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almanac_entries', function (Blueprint $table) {
            $table->string('responsible')->nullable()->after('activity');
            $table->string('category', 24)->nullable()->index()->after('responsible');
            $table->boolean('is_key_date')->default(false)->index()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('almanac_entries', function (Blueprint $table) {
            $table->dropColumn(['responsible', 'category', 'is_key_date']);
        });
    }
};
