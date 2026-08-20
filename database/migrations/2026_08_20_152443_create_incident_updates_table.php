<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The incident timeline. `is_ai_drafted` plus a null `published_at` is the
 * one-click-to-publish queue that the auto-updating wedge depends on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('investigating');
            $table->text('body');
            $table->boolean('is_ai_drafted')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'incident_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_updates');
    }
};
