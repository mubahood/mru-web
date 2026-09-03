<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Defaults true: every partner already on the homepage today keeps
        // showing the moment this ships. Curation from here on is opt-out,
        // not a silent mass-hide of everything that existed before this flag.
        Schema::table('partners', function (Blueprint $table) {
            $table->boolean('show_on_home')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('show_on_home');
        });
    }
};
