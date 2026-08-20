<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema only at this phase; the checking engine arrives in phase 6. Present
 * now so the isolation policies cover it before any data exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('http');
            $table->string('target')->nullable();
            $table->string('expected_keyword')->nullable();
            $table->unsignedSmallInteger('expected_status_code')->default(200);
            $table->unsignedInteger('interval_seconds')->default(60);
            $table->unsignedTinyInteger('failure_threshold')->default(3);
            $table->boolean('is_enabled')->default(true);

            /** Opening an incident without a human is the core wedge. */
            $table->boolean('auto_open_incident')->default(false);

            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->string('last_status')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
