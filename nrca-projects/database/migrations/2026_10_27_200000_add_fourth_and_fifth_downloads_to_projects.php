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
            $table->string('fourth_download')->nullable();
            $table->string('fourth_download_url')->nullable();
            $table->string('fifth_download')->nullable();
            $table->string('fifth_download_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'fourth_download',
                'fourth_download_url',
                'fifth_download',
                'fifth_download_url',
            ]);
        });
    }
};
