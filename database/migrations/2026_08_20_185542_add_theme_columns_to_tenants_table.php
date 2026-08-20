<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Presentation settings for a status page.
 *
 * These live on `tenants` rather than a table of their own because they are
 * strictly one-to-one with the page, and because the publisher reads them from
 * a queue worker where no tenant is resolved -- `tenants` is central, so it
 * stays readable without Row-Level Security context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('headline')->nullable();
            $table->string('support_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#4f46e5');
            $table->text('custom_css')->nullable();
            $table->string('timezone')->default('UTC');

            /** Free-tier pages carry the badge; it is the growth loop. */
            $table->boolean('show_powered_by')->default(true);

            /** When the last successful publish wrote to object storage. */
            $table->timestamp('last_published_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'headline', 'support_url', 'logo_path', 'primary_color',
                'custom_css', 'timezone', 'show_powered_by', 'last_published_at',
            ]);
        });
    }
};
