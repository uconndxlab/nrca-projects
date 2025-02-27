<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;

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
            $query->where('county', $request->county)->orderBy('county', 'asc');
        }

        if ($request->filled('program')) {
            $query->where('program', $request->program)->orderBy('name', 'asc');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('program', 'like', '%' . $request->search . '%')
                    ->orWhere('county', 'like', '%' . $request->search . '%');
            });
        }

        $projects = $query->paginate(50);
        $categories = Category::all();
        $years = Project::select('year')->distinct()->orderBy('year', 'asc')->pluck('year');
        $counties = Project::select('county')->distinct()->orderBy('county')->pluck('county');
        $programs = Project::select('program')->distinct()->orderBy('program')->pluck('program');

        return view('projects.index', compact('projects', 'categories', 'years', 'counties', 'programs'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('projects.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'county' => 'required|string|max:255',
            'thumbnail_image' => 'nullable|image|max:2048',
            'primary_product' => 'nullable|file|max:10000',
            'secondary_product' => 'nullable|file|max:10000',
        ]);

        $project = new Project($request->except(['thumbnail_image', 'primary_product', 'secondary_product']));

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


        // Attach the project to the selected categories


        $project->save();

        // Attach categories if any are selected
        if ($request->has('categories')) {
            $project->categories()->attach($request->input('categories'));
        }

        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project)
    {
        $categories = Category::all();
        return view('projects.edit', compact('project', 'categories'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'county' => 'required|string|max:255',
        ]);

        $project->update($request->except(['thumbnail_image', 'primary_product', 'secondary_product']));

        if ($request->hasFile('thumbnail_image')) {
            $project->thumbnail = $request->file('thumbnail_image')->store('thumbnails', 'public');
        }

        if ($request->hasFile('primary_product')) {
            $project->primary_product = $request->file('primary_product')->store('products', 'public');
        }

        if ($request->filled('primary_product_url')) {
            $project->primary_product_url = $request->input('primary_product_url');
        }

        if ($request->hasFile('secondary_product')) {
            $project->secondary_product = $request->file('secondary_product')->store('products', 'public');
        }

        if ($request->filled('secondary_product_url')) {
            $project->secondary_product_url = $request->input('secondary_product_url');
        }

        // Detach all categories first
        $project->categories()->detach();
        // Attach new categories if any are selected
        if ($request->has('categories')) {
            $project->categories()->attach($request->input('categories'));
        }

        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
