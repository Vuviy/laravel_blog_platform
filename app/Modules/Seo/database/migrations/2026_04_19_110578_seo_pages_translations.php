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
        Schema::create('seo_pages_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('seo_page_id');
            $table->string('locale', 5);

            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->string('seo_keywords', 255)->nullable();
            $table->string('seo_og_image')->nullable();

            $table->timestamps();

            $table->foreign('seo_page_id')
                ->references('id')
                ->on('seo_pages')
                ->onDelete('cascade');

            $table->unique(['seo_page_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_pages_translations');
    }
};
