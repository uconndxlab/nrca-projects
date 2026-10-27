<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use App\Models\Program;
use App\Models\CohortYear;
use App\Models\County;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the comprehensive Admin Hub.
     */
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'projects');

        // Projects Query for Admin Table
        $projectQuery = Project::with('categories');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $projectQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('program', 'like', "%{$search}%")
                  ->orWhere('county', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program')) {
            $projectQuery->where('program', $request->input('program'));
        }

        if ($request->filled('year')) {
            $projectQuery->where('year', $request->input('year'));
        }

        if ($request->filled('category')) {
            $catId = $request->input('category');
            $projectQuery->whereHas('categories', function ($q) use ($catId) {
                $q->where('categories.id', $catId);
            });
        }

        $projects = $projectQuery->orderBy('year', 'desc')->orderBy('title', 'asc')->paginate(20, ['*'], 'projects_page');

        // Programs with project count
        $programs = Program::orderBy('name', 'asc')->get()->map(function ($p) {
            $p->projects_count = Project::where('program', $p->name)->count();
            return $p;
        });

        // Categories / Topics with project count
        $categories = Category::withCount('projects')->orderBy('name', 'asc')->get();

        // Cohort Years with project count
        $years = CohortYear::orderBy('year', 'desc')->get()->map(function ($y) {
            $y->projects_count = Project::where('year', $y->year)->count();
            return $y;
        });

        // Counties with project count
        $counties = County::orderBy('name', 'asc')->get()->map(function ($c) {
            $c->projects_count = Project::where('county', 'like', "%{$c->name}%")->count();
            return $c;
        });

        // Users
        $users = User::orderBy('is_admin', 'desc')->orderBy('netid', 'asc')->get();

        // Overview stats
        $stats = [
            'total_projects' => Project::count(),
            'total_programs' => $programs->count(),
            'total_categories' => $categories->count(),
            'total_years' => $years->count(),
            'total_counties' => $counties->count(),
            'total_admins' => User::where('is_admin', true)->count(),
        ];

        $managedCounties = $counties;

        return view('admin.index', compact(
            'projects',
            'programs',
            'categories',
            'years',
            'counties',
            'managedCounties',
            'users',
            'stats',
            'activeTab'
        ));
    }

    /* -------------------------------------------------------------
     | Program Management
     * ------------------------------------------------------------*/

    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:programs,name',
            'full_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Program::create([
            'name' => strtoupper(trim($validated['name'])),
            'full_name' => $validated['full_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin', ['tab' => 'programs'])->with('success', "Program '{$validated['name']}' added successfully.");
    }

    public function updateProgram(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:programs,name,' . $program->id,
            'full_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $oldName = $program->name;
        $newName = strtoupper(trim($validated['name']));

        if ($oldName !== $newName) {
            Project::where('program', $oldName)->update(['program' => $newName]);
        }

        $program->update([
            'name' => $newName,
            'full_name' => $validated['full_name'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin', ['tab' => 'programs'])->with('success', "Program '{$newName}' updated successfully.");
    }

    public function destroyProgram(Program $program)
    {
        $name = $program->name;
        $program->delete();

        return redirect()->route('admin', ['tab' => 'programs'])->with('success', "Program '{$name}' deleted successfully.");
    }

    /* -------------------------------------------------------------
     | Cohort Year Management
     * ------------------------------------------------------------*/

    public function storeYear(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100|unique:years,year',
            'notes' => 'nullable|string|max:255',
        ]);

        CohortYear::create($validated);

        return redirect()->route('admin', ['tab' => 'years'])->with('success', "Cohort Year {$validated['year']} added successfully.");
    }

    public function updateYear(Request $request, CohortYear $year)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100|unique:years,year,' . $year->id,
            'notes' => 'nullable|string|max:255',
        ]);

        $oldYear = $year->year;
        $newYear = (int)$validated['year'];

        if ($oldYear !== $newYear) {
            Project::where('year', $oldYear)->update(['year' => $newYear]);
        }

        $year->update($validated);

        return redirect()->route('admin', ['tab' => 'years'])->with('success', "Year {$newYear} updated successfully.");
    }

    public function destroyYear(CohortYear $year)
    {
        $val = $year->year;
        $year->delete();

        return redirect()->route('admin', ['tab' => 'years'])->with('success', "Cohort Year {$val} deleted successfully.");
    }

    /* -------------------------------------------------------------
     | County Management
     * ------------------------------------------------------------*/

    public function storeCounty(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:counties,name',
        ]);

        $name = ucwords(trim($validated['name']));
        County::create(['name' => $name]);

        return redirect()->route('admin', ['tab' => 'counties'])->with('success', "County '{$name}' added successfully.");
    }

    public function updateCounty(Request $request, County $county)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:counties,name,' . $county->id,
        ]);

        $oldName = $county->name;
        $newName = ucwords(trim($validated['name']));

        if ($oldName !== $newName) {
            // Update comma-separated county values in projects
            $affected = Project::where('county', 'like', "%{$oldName}%")->get();
            foreach ($affected as $p) {
                $pieces = array_map('trim', explode(',', $p->county));
                $pieces = array_map(fn($item) => $item === $oldName ? $newName : $item, $pieces);
                $p->county = implode(', ', $pieces);
                $p->save();
            }
        }

        $county->update(['name' => $newName]);

        return redirect()->route('admin', ['tab' => 'counties'])->with('success', "County updated to '{$newName}'.");
    }

    public function destroyCounty(County $county)
    {
        $name = $county->name;
        $county->delete();

        return redirect()->route('admin', ['tab' => 'counties'])->with('success', "County '{$name}' deleted successfully.");
    }

    /* -------------------------------------------------------------
     | Staff User Management
     * ------------------------------------------------------------*/

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'netid' => 'required|string|max:50|unique:users,netid',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_admin' => 'nullable|boolean',
        ]);

        User::create([
            'netid' => trim($validated['netid']),
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_admin' => $request->boolean('is_admin', true),
        ]);

        return redirect()->route('admin', ['tab' => 'users'])->with('success', "Staff user '{$validated['netid']}' added successfully.");
    }

    public function toggleUserAdmin(User $user)
    {
        if (auth()->user()?->id === $user->id && $user->is_admin) {
            return redirect()->route('admin', ['tab' => 'users'])->with('error', 'You cannot revoke your own admin access.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $action = $user->is_admin ? 'granted admin privileges' : 'removed from admin group';
        return redirect()->route('admin', ['tab' => 'users'])->with('success', "User '{$user->netid}' has been {$action}.");
    }

    public function destroyUser(User $user)
    {
        if (auth()->user()?->id === $user->id) {
            return redirect()->route('admin', ['tab' => 'users'])->with('error', 'You cannot delete your own user account.');
        }

        $netid = $user->netid;
        $user->delete();

        return redirect()->route('admin', ['tab' => 'users'])->with('success', "User '{$netid}' deleted successfully.");
    }

    public function normalizeCounties()
    {
        $replacements = [
            'Newlondon' => 'New London',
            'Newhaven' => 'New Haven',
            'Harford' => 'Hartford',
            'Fairfield, Litchfield, Hartford, Middlesex, Newhaven, Newlondon, Windham' => 'Statewide',
        ];

        $updatedCount = 0;
        foreach ($replacements as $old => $new) {
            $updatedCount += Project::where('county', $old)->update(['county' => $new]);
        }

        return redirect()->route('admin', ['tab' => 'counties'])->with('success', "Normalized {$updatedCount} project county records.");
    }
}
