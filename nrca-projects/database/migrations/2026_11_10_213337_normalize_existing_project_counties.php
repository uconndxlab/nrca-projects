<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('projects')) {
            return;
        }

        // 1. Direct replacements for statewide list
        DB::table('projects')
            ->where('county', 'Fairfield, Litchfield, Hartford, Middlesex, Newhaven, Newlondon, Windham')
            ->update(['county' => 'Statewide']);

        // 2. Specific town corrections to standard Connecticut county
        DB::table('projects')
            ->where('county', 'Darien')
            ->update(['county' => 'Fairfield']);

        // 3. Typo/casing fixes for individual or combined county values
        $projects = DB::table('projects')
            ->where('county', 'like', '%Newhaven%')
            ->orWhere('county', 'like', '%Newlondon%')
            ->orWhere('county', 'like', '%Harford%')
            ->get(['id', 'county']);

        foreach ($projects as $project) {
            $updated = str_replace(
                ['Newhaven', 'Newlondon', 'Harford'],
                ['New Haven', 'New London', 'Hartford'],
                $project->county
            );

            if ($updated !== $project->county) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['county' => $updated]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Destructive migration
    }
};
