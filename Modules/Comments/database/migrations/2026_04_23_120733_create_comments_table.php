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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('content')->nullable();
            $table->uuid('parent_id')->nullable()->index();
            $table->integer('lft')->unsigned();
            $table->integer('rgt')->unsigned();
            $table->integer('depth')->unsigned()->default(0);
            $table->uuidMorphs('entity');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'created_at']);
            $table->index(['entity_type', 'entity_id', 'lft']);
            $table->index(['entity_type', 'entity_id', 'rgt']);
            $table->index(['entity_type', 'entity_id', 'status']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('comments')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
