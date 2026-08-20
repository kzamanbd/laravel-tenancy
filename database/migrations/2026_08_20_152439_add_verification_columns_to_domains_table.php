<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ownership must be proven by CNAME before a certificate is issued, otherwise
 * free-tier custom domains become a phishing vector. The `ask` endpoint that
 * on-demand TLS calls reads `verified_at` from here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false);
            $table->string('verification_token')->nullable();
            $table->string('verification_status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
            $table->timestamp('certificate_expires_at')->nullable();

            $table->index(['verification_status', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['verification_status', 'verified_at']);
            $table->dropColumn([
                'is_primary', 'verification_token', 'verification_status',
                'verified_at', 'last_checked_at',
                'certificate_issued_at', 'certificate_expires_at',
            ]);
        });
    }
};
