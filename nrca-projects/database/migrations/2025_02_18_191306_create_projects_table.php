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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('title');
            $table->string('county');
            $table->string('thumbnail')->nullable();
            $table->string('primary_product')->nullable();
            $table->string('primary_product_url')->nullable();
            $table->string('secondary_product')->nullable();
            $table->string('secondary_product_url')->nullable();
            $table->string('program')->nullable();


            $table->timestamps();
        });

        Schema::create('category_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
        });

        
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
