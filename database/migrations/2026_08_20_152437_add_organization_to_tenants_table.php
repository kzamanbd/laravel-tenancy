<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tenant is one workspace, which is one status page. Nesting it under an
 * organization now costs a single join; retrofitting the level into a live
 * billing system does not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();
            $table->string('name')->nullable()->after('organization_id');
            $table->string('slug')->nullable()->unique()->after('name');
            $table->timestamp('published_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'name', 'slug', 'published_at']);
        });
    }
};
