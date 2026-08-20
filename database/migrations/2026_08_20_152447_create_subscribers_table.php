<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Double opt-in is modelled from day one: a subscriber is only deliverable
 * once `confirmed_at` is set. Shared sending reputation makes this
 * non-negotiable rather than a nicety.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default('email');

            /** Email address, webhook URL, Slack webhook, or E.164 number. */
            $table->string('endpoint');

            $table->string('confirmation_token')->nullable();
            $table->string('unsubscribe_token')->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            /** Null means every component on the page. */
            $table->json('component_ids')->nullable();

            $table->unsignedInteger('bounce_count')->default(0);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'channel', 'endpoint']);
            $table->index(['tenant_id', 'confirmed_at', 'unsubscribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
