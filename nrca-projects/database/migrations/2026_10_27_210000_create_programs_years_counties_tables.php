<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('programs')) {
            Schema::create('programs', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('full_name')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('years')) {
            Schema::create('years', function (Blueprint $table) {
                $table->id();
                $table->integer('year')->unique();
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('counties')) {
            Schema::create('counties', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // Seed initial programs
        $programs = [
            [
                'name' => 'CAP',
                'full_name' => 'Conservation Ambassador Program (CAP)',
                'description' => 'High school students completing real-world community conservation projects.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CTP',
                'full_name' => 'Conservation Training Partnerships (CTP)',
                'description' => 'Community and student teams using online mapping tools for local conservation.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EDS',
                'full_name' => 'Environmental Data Science (EDS)',
                'description' => 'Students analyzing environmental datasets and developing web applications.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($programs as $prog) {
            DB::table('programs')->updateOrInsert(['name' => $prog['name']], $prog);
        }

        // Seed distinct years from existing projects
        if (Schema::hasTable('projects')) {
            $existingYears = DB::table('projects')->whereNotNull('year')->distinct()->orderBy('year', 'asc')->pluck('year');
            foreach ($existingYears as $y) {
                if ($y > 0) {
                    DB::table('years')->updateOrInsert(
                        ['year' => $y],
                        ['year' => $y, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // Seed default Connecticut counties
        $counties = [
            'Fairfield',
            'Hartford',
            'Litchfield',
            'Middlesex',
            'New Haven',
            'New London',
            'Tolland',
            'Windham',
            'Statewide',
        ];

        foreach ($counties as $c) {
            DB::table('counties')->updateOrInsert(
                ['name' => $c],
                ['name' => $c, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
        Schema::dropIfExists('years');
        Schema::dropIfExists('counties');
    }
};
