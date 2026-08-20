<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The billing entity. An organization owns one or more tenants (status pages).
 * An agency is simply an organization whose `is_agency` flag is set, which is
 * what lets reseller billing land later without a hierarchy retrofit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->default('free');
            $table->boolean('is_agency')->default(false);
            $table->foreignId('parent_organization_id')->nullable()
                ->constrained('organizations')->nullOnDelete();
            $table->unsignedInteger('tenant_quota')->default(1);
            $table->string('billing_email')->nullable();
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('parent_organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
