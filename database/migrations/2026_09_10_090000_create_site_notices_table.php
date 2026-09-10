<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The announcement strip that runs above the header.
 *
 * A notice is content, not markup: an editor writes one, chooses how it should
 * look, says when it should start and stop, and it appears and disappears on
 * its own. Nobody should have to deploy to take down a notice about an intake
 * that closed last week.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_notices', function (Blueprint $table) {
            $table->id();

            $table->string('message', 300);
            $table->string('label', 60)->nullable();      // the small tag: "Now open", "Deadline"
            $table->string('link_url', 500)->nullable();
            $table->string('link_label', 60)->nullable();

            // How it should look. Kept as a string rather than an enum so a new
            // template is a view + a case, not a migration.
            $table->string('template', 24)->default('ticker');
            $table->string('icon', 40)->nullable();

            // When it is allowed to show. Null on either side means unbounded,
            // so "from now until further notice" needs no dates at all.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // A countdown reads off this rather than off ends_at: the deadline
            // a reader cares about is often not the moment the strip retires.
            $table->timestamp('deadline_at')->nullable();

            $table->boolean('is_published')->default(true);
            $table->boolean('is_dismissible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            // The public query is "published, and inside its window, best first".
            $table->index(['is_published', 'starts_at', 'ends_at']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_notices');
    }
};
