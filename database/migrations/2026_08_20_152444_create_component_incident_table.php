<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carries `tenant_id` even though it is derivable through either parent, so a
 * single RLS policy shape applies uniformly to every tenant-scoped table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_incident', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('degraded_performance');

            $table->unique(['incident_id', 'component_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_incident');
    }
};
