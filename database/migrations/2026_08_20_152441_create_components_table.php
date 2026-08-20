<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('operational');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_public')->default(true);
            $table->boolean('show_uptime')->default(true);
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
