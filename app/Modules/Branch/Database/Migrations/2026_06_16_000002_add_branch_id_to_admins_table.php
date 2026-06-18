<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branch assignment for staff accounts — the column behind branch-scoped RBAC.
 *
 * Owned by the Branch module (remove the module, lose the column). The column is
 * always nullable + indexed (null = unscoped staff). On every driver except
 * SQLite we add a real foreign key: CI and production run PostgreSQL, where
 * `ALTER TABLE ... ADD CONSTRAINT` is supported. SQLite (local dev) cannot add a
 * foreign key to an existing table via ALTER, so there the column is plain and
 * integrity is enforced at the application layer (BranchScope / BranchPolicy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('email')->index();
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('admins', function (Blueprint $table): void {
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['branch_id']);
            }

            $table->dropColumn('branch_id');
        });
    }
};
