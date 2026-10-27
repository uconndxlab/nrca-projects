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
        $categories = Category::all();
        $programs = Program::orderBy('name')->get();
        return view('projects.create', compact('categories', 'programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'counties' => 'nullable|array',
            'counties.*' => 'string|max:255',
            'county' => 'nullable|string|max:255',
            'thumbnail_image' => 'nullable|image|max:2048',
            'primary_product' => 'nullable|file|max:10000|required_without:primary_product_url',
            'primary_product_url' => 'nullable|url|max:2048|required_without:primary_product',
            'secondary_product' => 'nullable|file|max:10000',
            'secondary_product_url' => 'nullable|url|max:2048',
            'third_download' => 'nullable|file|max:10000',
            'third_download_url' => 'nullable|url|max:2048',
            'fourth_download' => 'nullable|file|max:10000',
            'fourth_download_url' => 'nullable|url|max:2048',
            'fifth_download' => 'nullable|file|max:10000',
            'fifth_download_url' => 'nullable|url|max:2048',
        ], [
            'primary_product.required_without' => 'Please provide at least one media product (upload a file or website link).',
            'primary_product_url.required_without' => 'Please provide at least one media product (upload a file or website link).',
        ]);

        $countiesInput = $request->input('counties', []);
        if (is_array($countiesInput) && count($countiesInput) > 0) {
            $county = implode(', ', array_filter($countiesInput));
        } else {
            $county = $request->input('county');
        }

        if (empty($county)) {
            return back()->withInput()->withErrors([
                'counties' => 'Please select at least one county.',
            ]);
        }

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
        $categories = Category::all();
        $programs = Program::orderBy('name')->get();
        return view('projects.edit', compact('project', 'categories', 'programs'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'counties' => 'nullable|array',
            'counties.*' => 'string|max:255',
            'county' => 'nullable|string|max:255',
            'thumbnail_image' => 'nullable|image|max:2048',
            'primary_product' => 'nullable|file|max:10000',
            'primary_product_url' => 'nullable|url|max:2048',
            'secondary_product' => 'nullable|file|max:10000',
            'secondary_product_url' => 'nullable|url|max:2048',
            'third_download' => 'nullable|file|max:10000',
            'third_download_url' => 'nullable|url|max:2048',
            'fourth_download' => 'nullable|file|max:10000',
            'fourth_download_url' => 'nullable|url|max:2048',
            'fifth_download' => 'nullable|file|max:10000',
            'fifth_download_url' => 'nullable|url|max:2048',
        ]);

        $countiesInput = $request->input('counties', []);
        if (is_array($countiesInput) && count($countiesInput) > 0) {
            $county = implode(', ', array_filter($countiesInput));
        } else {
            $county = $request->input('county');
        }

        if (empty($county)) {
            return back()->withInput()->withErrors([
                'counties' => 'Please select at least one county.',
            ]);
        }

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
