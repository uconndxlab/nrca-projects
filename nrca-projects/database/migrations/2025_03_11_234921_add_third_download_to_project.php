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
        Schema::table('projects', function (Blueprint $table) {
            // $table->string('secondary_product')->nullable();
            // $table->string('secondary_product_url')->nullable();

            $table->string('third_download')->nullable();
            $table->string('third_download_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('third_download');
            $table->dropColumn('third_download_url');
        });
    }
};
