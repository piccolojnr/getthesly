<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->char('access_token_hash', 64)->index();
            $table->unsignedInteger('current_revision')->default(0);
            $table->timestamps();
        });

        Schema::create('research_draft_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('research_draft_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision');
            $table->text('project_title');
            $table->text('research_question');
            $table->text('interpretation')->nullable();
            $table->string('status', 16);
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['research_draft_id', 'revision']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_draft_revisions');
        Schema::dropIfExists('research_drafts');
    }
};
