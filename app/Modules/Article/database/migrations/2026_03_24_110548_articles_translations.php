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
        Schema::create('article_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('article_id');
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->timestamps();

            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade');

            $table->unique(['article_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_translations');
    }
};
