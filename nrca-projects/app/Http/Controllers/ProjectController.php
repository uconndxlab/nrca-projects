<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use App\Models\Program;
use App\Models\CohortYear;
use App\Models\County;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
            $q->where('id', $request->category);
            });
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year)->orderBy('year', 'asc');
        }

        if ($request->filled('county')) {
            $selectedCounty = $request->county;
            $query->where(function ($q) use ($selectedCounty) {
                $q->where('county', 'like', '%' . $selectedCounty . '%');
                if ($selectedCounty === 'New Haven') {
                    $q->orWhere('county', 'like', '%Newhaven%');
                } elseif ($selectedCounty === 'New London') {
                    $q->orWhere('county', 'like', '%Newlondon%');
                }
            });
        }

        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('program', 'like', '%' . $request->search . '%')
                    ->orWhere('county', 'like', '%' . $request->search . '%');
            });
        }

        $projects = $query->orderBy('year', 'desc')->orderBy('title', 'asc')->paginate(24);
        $categories = Category::all();
        $years = CohortYear::orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years = Project::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        }
        $counties = County::orderBy('name')->pluck('name');
        if ($counties->isEmpty()) {
            $counties = collect(['Fairfield', 'Hartford', 'Litchfield', 'Middlesex', 'New Haven', 'New London', 'Tolland', 'Windham', 'Statewide']);
        }
        $programs = Program::orderBy('name')->pluck('name');
        if ($programs->isEmpty()) {
            $programs = Project::select('program')->distinct()->orderBy('program')->pluck('program');
        }

        return view('projects.index', compact('projects', 'categories', 'years', 'counties', 'programs'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $years = CohortYear::orderBy('year', 'desc')->pluck('year');
        return view('projects.create', compact('categories', 'programs', 'years'));
    }

    public function store(Request $request)
    {
        // Normalize single county input to counties array if provided
        if (!$request->has('counties') && $request->filled('county')) {
            $request->merge(['counties' => [$request->input('county')]]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|exists:programs,name',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'counties' => 'required|array|min:1',
            'counties.*' => 'string|exists:counties,name',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'thumbnail_image' => 'nullable|image|max:10240',
            'primary_product' => 'nullable|file|max:25600|required_without:primary_product_url',
            'primary_product_url' => 'nullable|url|max:2048|required_without:primary_product',
            'secondary_product' => 'nullable|file|max:25600',
            'secondary_product_url' => 'nullable|url|max:2048',
            'third_download' => 'nullable|file|max:25600',
            'third_download_url' => 'nullable|url|max:2048',
            'fourth_download' => 'nullable|file|max:25600',
            'fourth_download_url' => 'nullable|url|max:2048',
            'fifth_download' => 'nullable|file|max:25600',
            'fifth_download_url' => 'nullable|url|max:2048',
        ], [
            'program.exists' => 'The selected NRCA program is invalid.',
            'year.min' => 'Cohort year must be 2000 or later.',
            'year.max' => 'Cohort year must be allowed by an administrator.',
            'counties.required' => 'Please select at least one county.',
            'counties.min' => 'Please select at least one county.',
            'counties.*.exists' => 'One or more selected counties are invalid.',
            'categories.*.exists' => 'One or more selected tags are invalid.',
            'primary_product.required_without' => 'Please provide at least one media product.',
            'primary_product_url.required_without' => 'Please provide at least one media product.',
            'thumbnail_image.max' => 'The thumbnail image may not be greater than 10MB.',
            'primary_product.max' => 'The primary media file may not be greater than 25MB.',
            'secondary_product.max' => 'The secondary media file may not be greater than 25MB.',
            'third_download.max' => 'Deliverable file may not be greater than 25MB.',
            'fourth_download.max' => 'Deliverable file may not be greater than 25MB.',
            'fifth_download.max' => 'Deliverable file may not be greater than 25MB.',
        ]);

        $county = implode(', ', array_filter($request->input('counties', [])));

        $project = new Project($request->except([
            'thumbnail_image',
            'primary_product',
            'secondary_product',
            'third_download',
            'fourth_download',
            'fifth_download',
            'categories',
            'counties',
            'county',
        ]));
        $project->county = $county;

        if ($request->hasFile('thumbnail_image')) {
            $project->thumbnail = $request->file('thumbnail_image')->store('thumbnails', 'public');
        }

        if ($request->hasFile('primary_product')) {
            $project->primary_product = $request->file('primary_product')->store('products', 'public');
        }

        if ($request->filled('primary_product_url')) {
            $project->primary_product_url = $request->input('primary_product_url');
        }
        if ($request->filled('secondary_product_url')) {
            $project->secondary_product_url = $request->input('secondary_product_url');
        }

        if ($request->hasFile('secondary_product')) {
            $project->secondary_product = $request->file('secondary_product')->store('products', 'public');
        }

        if ($request->filled('third_download_url')) {
            $project->third_download_url = $request->input('third_download_url');
        }

        if ($request->hasFile('third_download')) {
            $project->third_download = $request->file('third_download')->store('products', 'public');
        }

        if ($request->filled('fourth_download_url')) {
            $project->fourth_download_url = $request->input('fourth_download_url');
        }

        if ($request->hasFile('fourth_download')) {
            $project->fourth_download = $request->file('fourth_download')->store('products', 'public');
        }

        if ($request->filled('fifth_download_url')) {
            $project->fifth_download_url = $request->input('fifth_download_url');
        }

        if ($request->hasFile('fifth_download')) {
            $project->fifth_download = $request->file('fifth_download')->store('products', 'public');
        }

        // Attach the project to the selected categories
        $project->save();

        // Attach categories if any are selected
        if ($request->has('categories')) {
            $project->categories()->attach($request->input('categories'));
        }

        return redirect()->route('admin')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        $categories = Category::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $years = CohortYear::orderBy('year', 'desc')->pluck('year');
        return view('projects.edit', compact('project', 'categories', 'programs', 'years'));
    }

    public function update(Request $request, Project $project)
    {
        // Normalize single county input to counties array if provided
        if (!$request->has('counties') && $request->filled('county')) {
            $request->merge(['counties' => [$request->input('county')]]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|exists:programs,name',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'counties' => 'required|array|min:1',
            'counties.*' => 'string|exists:counties,name',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'thumbnail_image' => 'nullable|image|max:10240',
            'primary_product' => 'nullable|file|max:25600',
            'primary_product_url' => 'nullable|url|max:2048',
            'secondary_product' => 'nullable|file|max:25600',
            'secondary_product_url' => 'nullable|url|max:2048',
            'third_download' => 'nullable|file|max:25600',
            'third_download_url' => 'nullable|url|max:2048',
            'fourth_download' => 'nullable|file|max:25600',
            'fourth_download_url' => 'nullable|url|max:2048',
            'fifth_download' => 'nullable|file|max:25600',
            'fifth_download_url' => 'nullable|url|max:2048',
        ], [
            'program.exists' => 'The selected NRCA program is invalid. Please select a defined program.',
            'year.min' => 'Cohort year must be 2000 or later.',
            'year.max' => 'Cohort year cannot be more than one year in the future.',
            'counties.required' => 'Please select at least one county.',
            'counties.min' => 'Please select at least one county.',
            'counties.*.exists' => 'One or more selected counties are invalid.',
            'categories.*.exists' => 'One or more selected tags are invalid.',
            'thumbnail_image.max' => 'The thumbnail image may not be greater than 10MB.',
            'primary_product.max' => 'The primary media file may not be greater than 25MB.',
            'secondary_product.max' => 'The secondary media file may not be greater than 25MB.',
            'third_download.max' => 'Deliverable #3 file may not be greater than 25MB.',
            'fourth_download.max' => 'Deliverable #4 file may not be greater than 25MB.',
            'fifth_download.max' => 'Deliverable #5 file may not be greater than 25MB.',
        ]);

        $county = implode(', ', array_filter($request->input('counties', [])));

        if (!$request->hasFile('primary_product') && !$project->primary_product && !$request->filled('primary_product_url')) {
            return back()->withInput()->withErrors([
                'primary_product' => 'Please provide at least one media product (upload a file or website link).',
            ]);
        }

        $project->update($request->except([
            'thumbnail_image',
            'primary_product',
            'secondary_product',
            'third_download',
            'fourth_download',
            'fifth_download',
            'remove_secondary_product',
            'remove_third_download',
            'remove_fourth_download',
            'remove_fifth_download',
            'counties',
            'county',
        ]));
        $project->county = $county;

        if ($request->hasFile('thumbnail_image')) {
            if ($project->thumbnail && Storage::disk('public')->exists($project->thumbnail)) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $project->thumbnail = $request->file('thumbnail_image')->store('thumbnails', 'public');
        }

        if ($request->hasFile('primary_product')) {
            if ($project->primary_product && Storage::disk('public')->exists($project->primary_product)) {
                Storage::disk('public')->delete($project->primary_product);
            }
            $project->primary_product = $request->file('primary_product')->store('products', 'public');
        }
        $project->primary_product_url = $request->input('primary_product_url');

        if ($request->boolean('remove_secondary_product')) {
            if ($project->secondary_product && Storage::disk('public')->exists($project->secondary_product)) {
                Storage::disk('public')->delete($project->secondary_product);
            }
            $project->secondary_product = null;
            $project->secondary_product_url = null;
        } else {
            if ($request->hasFile('secondary_product')) {
                if ($project->secondary_product && Storage::disk('public')->exists($project->secondary_product)) {
                    Storage::disk('public')->delete($project->secondary_product);
                }
                $project->secondary_product = $request->file('secondary_product')->store('products', 'public');
            }
            if ($request->has('secondary_product_url')) {
                $project->secondary_product_url = $request->input('secondary_product_url');
            }
        }

        if ($request->boolean('remove_third_download')) {
            if ($project->third_download && Storage::disk('public')->exists($project->third_download)) {
                Storage::disk('public')->delete($project->third_download);
            }
            $project->third_download = null;
            $project->third_download_url = null;
        } else {
            if ($request->hasFile('third_download')) {
                if ($project->third_download && Storage::disk('public')->exists($project->third_download)) {
                    Storage::disk('public')->delete($project->third_download);
                }
                $project->third_download = $request->file('third_download')->store('products', 'public');
            }
            if ($request->has('third_download_url')) {
                $project->third_download_url = $request->input('third_download_url');
            }
        }

        if ($request->boolean('remove_fourth_download')) {
            if ($project->fourth_download && Storage::disk('public')->exists($project->fourth_download)) {
                Storage::disk('public')->delete($project->fourth_download);
            }
            $project->fourth_download = null;
            $project->fourth_download_url = null;
        } else {
            if ($request->hasFile('fourth_download')) {
                if ($project->fourth_download && Storage::disk('public')->exists($project->fourth_download)) {
                    Storage::disk('public')->delete($project->fourth_download);
                }
                $project->fourth_download = $request->file('fourth_download')->store('products', 'public');
            }
            if ($request->has('fourth_download_url')) {
                $project->fourth_download_url = $request->input('fourth_download_url');
            }
        }

        if ($request->boolean('remove_fifth_download')) {
            if ($project->fifth_download && Storage::disk('public')->exists($project->fifth_download)) {
                Storage::disk('public')->delete($project->fifth_download);
            }
            $project->fifth_download = null;
            $project->fifth_download_url = null;
        } else {
            if ($request->hasFile('fifth_download')) {
                if ($project->fifth_download && Storage::disk('public')->exists($project->fifth_download)) {
                    Storage::disk('public')->delete($project->fifth_download);
                }
                $project->fifth_download = $request->file('fifth_download')->store('products', 'public');
            }
            if ($request->has('fifth_download_url')) {
                $project->fifth_download_url = $request->input('fifth_download_url');
            }
        }

        // Categories
        $project->categories()->detach();
        if ($request->has('categories')) {
            $project->categories()->attach($request->input('categories'));
        }

        $project->save();

        return redirect()->route('admin')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back()->with('success', 'Project deleted successfully.');
    }
}
