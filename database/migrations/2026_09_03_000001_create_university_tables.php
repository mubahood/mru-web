<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The university content model: faculties, programmes, people, events, the
 * academic almanac, scholarships, vacancies and partners.
 *
 * Shapes follow the legacy custom-CMS tables they are migrated from (see
 * docs/02-OLD-SITE-ANALYSIS.md §2) with the model app's conventions layered
 * on: slugs for routing, is_published gates, sort_order, JSON for the
 * list-shaped fields the old site stored as JSON text.
 *
 * faculties.dean_staff_id and staff_members.faculty_id reference each other,
 * so the dean column gains its constraint only after both tables exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name', 20)->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->longText('about')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('color', 20)->default('#05275C');
            $table->string('icon', 50)->default('fa-building-columns');
            $table->json('departments')->nullable();
            $table->json('careers')->nullable();
            $table->unsignedBigInteger('dean_staff_id')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            // leadership | dean | lecturer | administrative | council | guild | committee | other
            $table->string('staff_role', 30)->default('other')->index();
            // The body a person belongs to when the role alone is not enough:
            // "University Council", "Management Committee", "Students' Guild".
            $table->string('group_label', 100)->nullable()->index();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->text('bio')->nullable();
            $table->text('education')->nullable();
            $table->string('photo')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->foreign('dean_staff_id')->references('id')->on('staff_members')->nullOnDelete();
        });

        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('award_code', 30)->nullable();
            // certificate | diploma | bachelor | postgraduate_diploma | masters | phd
            $table->string('level', 30)->default('bachelor')->index();
            $table->string('duration', 50)->nullable();
            $table->json('study_modes')->nullable();
            $table->unsignedBigInteger('tuition_per_semester')->nullable();
            $table->string('tuition_currency', 3)->default('UGX');
            // Carries "verify with the Bursar" style caveats — the legacy fee
            // data was scraped from a PDF and not every row could be trusted.
            $table->string('tuition_note')->nullable();
            $table->text('entry_requirements')->nullable();
            $table->longText('description')->nullable();
            $table->text('career_prospects')->nullable();
            $table->json('intake_months')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('university_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 300)->nullable();
            $table->longText('description')->nullable();
            $table->string('venue')->nullable();
            $table->string('campus', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('almanac_entries', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 20)->index();
            $table->string('semester', 40);
            $table->string('period', 80)->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('activity', 300);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 50)->nullable();
            $table->string('coverage', 120)->nullable();
            $table->text('criteria')->nullable();
            $table->string('amount_note')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('reference_no', 50)->nullable();
            $table->string('department')->nullable();
            $table->string('type', 40)->nullable();
            $table->string('location', 80)->nullable();
            $table->date('deadline_on')->nullable()->index();
            $table->text('summary')->nullable();
            $table->text('requirements')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('faculties', fn (Blueprint $table) => $table->dropForeign(['dean_staff_id']));
        Schema::dropIfExists('partners');
        Schema::dropIfExists('vacancies');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('almanac_entries');
        Schema::dropIfExists('university_events');
        Schema::dropIfExists('programmes');
        Schema::dropIfExists('staff_members');
        Schema::dropIfExists('faculties');
    }
};
