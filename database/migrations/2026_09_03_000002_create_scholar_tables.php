<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MRU Scholar — the university's research repository.
 *
 * Modelled on the richer of the two legacy implementations (the WordPress
 * plugin schema, docs/02-OLD-SITE-ANALYSIS.md §1.6): scholar profiles,
 * publications with a moderation status, multi-author support that admits
 * external co-authors, and a research-area taxonomy.
 *
 * Ships admin-managed; the status/approved_by fields are here so lecturer
 * self-submission can be turned on later without a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('scholars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('staff_members')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title', 100)->nullable();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->string('department')->nullable();
            $table->text('bio')->nullable();
            $table->text('research_interests')->nullable();
            $table->string('photo')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('email')->nullable();
            $table->string('google_scholar_url')->nullable();
            $table->string('orcid', 30)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->string('slug', 220)->unique();
            $table->text('abstract')->nullable();
            // journal | conference | book | book_chapter | thesis | report
            $table->string('type', 30)->default('journal')->index();
            $table->string('journal_name')->nullable();
            $table->string('publisher')->nullable();
            $table->string('volume', 50)->nullable();
            $table->string('issue', 50)->nullable();
            $table->string('pages', 50)->nullable();
            $table->date('publication_date')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('doi', 120)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('keywords', 500)->nullable();
            $table->unsignedInteger('citations')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            // draft | pending | published
            $table->string('status', 20)->default('published')->index();
            $table->boolean('is_featured')->default(false);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('publication_author', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            // Either an MRU scholar or a named external co-author.
            $table->foreignId('scholar_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('external_name')->nullable();
            $table->unsignedTinyInteger('author_order')->default(0);
        });

        Schema::create('publication_research_area', function (Blueprint $table) {
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_area_id')->constrained()->cascadeOnDelete();
            $table->primary(['publication_id', 'research_area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_research_area');
        Schema::dropIfExists('publication_author');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('scholars');
        Schema::dropIfExists('research_areas');
    }
};
