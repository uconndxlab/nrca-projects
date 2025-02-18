<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::paginate(50);
        $categories = Category::all();
        return view('projects.index', compact('projects', 'categories'));
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

        if ($request->hasFile('secondary_product')) {
            $project->secondary_product = $request->file('secondary_product')->store('products', 'public');
        }

        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'project_title' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'county' => 'required|string|max:255',
        ]);

        $project->update($request->except(['thumbnail_image', 'primary_product', 'secondary_product']));

        if ($request->hasFile('thumbnail_image')) {
            $project->thumbnail_image = $request->file('thumbnail_image')->store('thumbnails', 'public');
        }

        if ($request->hasFile('primary_product')) {
            $project->primary_product = $request->file('primary_product')->store('products', 'public');
        }

        if ($request->hasFile('secondary_product')) {
            $project->secondary_product = $request->file('secondary_product')->store('products', 'public');
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
