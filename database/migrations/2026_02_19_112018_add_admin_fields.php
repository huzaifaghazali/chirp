<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add admin fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('avatar');
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active')->after('is_admin');
            $table->timestamp('suspended_until')->nullable()->after('status');
        });

        // Admin activity logs
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // 'ban_user', 'delete_chirp', 'suspend_user'
            $table->morphs('target'); // user or chirp
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });

        // User reports for moderation
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable'); // chirp, user
            $table->foreignId('reporter_id')->constrained('users');
            $table->enum('reason', ['spam', 'harassment', 'misinformation', 'hate_speech', 'violence', 'other']);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();
            $table->index(['status', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'status', 'suspended_until']);
        });
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('reports');
    }
};
