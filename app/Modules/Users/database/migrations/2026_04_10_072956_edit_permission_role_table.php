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
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropPrimary(['role_id', 'permission_id']);
            $table->dropColumn('permission_id');
            $table->string('permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropPrimary(['role_id', 'permission']);

            $table->dropIndex(['permission']);
            $table->dropColumn('permission');

            $table->foreignUuid('permission_id');

            $table->primary(['role_id', 'permission_id']);

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();
        });
    }
};
