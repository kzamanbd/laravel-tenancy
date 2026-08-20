<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('status')->default('investigating');
            $table->string('impact')->default('minor');
            $table->boolean('is_published')->default(false);

            /** Set when the incident was opened by a monitor rather than a human. */
            $table->foreignId('opened_by_monitor_id')->nullable();
            $table->boolean('notifications_sent')->default(false);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
