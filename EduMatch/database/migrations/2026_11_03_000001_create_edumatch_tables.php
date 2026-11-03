<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('mentee');
            $table->string('career')->nullable();
            $table->unsignedTinyInteger('academic_year')->nullable();
            $table->text('bio')->nullable();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->timestamps();
            $table->unique(['user_id', 'subject_id', 'kind']);
        });

        Schema::create('mentor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
            $table->unique(
                ['user_id', 'day_of_week', 'starts_at', 'ends_at'],
                'mentor_availabilities_slot_unique'
            );
        });

        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->unsignedTinyInteger('duration')->default(45);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['mentor_id', 'starts_at']);
            $table->index(['mentee_id', 'starts_at']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('mentoring_sessions');
        Schema::dropIfExists('mentor_availabilities');
        Schema::dropIfExists('subject_user');
        Schema::dropIfExists('subjects');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'career', 'academic_year', 'bio']);
        });
    }
};
