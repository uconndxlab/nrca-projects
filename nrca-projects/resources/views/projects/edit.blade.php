@extends('layouts.app')
@section('title', 'Edit Project')
@section('content')
    <div class="container">
        <h1 class="mb-4">Edit Project</h1>

        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Project Title --}}
            <div class="mb-3">
                <label class="form-label">Project Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $project->title) }}" required>
            </div>

            {{-- Program --}}
            <div class="mb-3">
                <label class="form-label">Program</label>
                <input type="text" name="program" class="form-control" value="{{ old('program', $project->program) }}" required>
            </div>

            {{-- Year --}}
            <div class="mb-3">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="{{ old('year', $project->year) }}" required>
            </div>

            {{-- County --}}
            <div class="mb-3">
                <label class="form-label">County</label>
                <select name="county" class="form-select" required>
                    <option value="">Select County</option>
                    @foreach($counties as $county)
                        <option value="{{ $county }}" {{ old('county', $project->county) == $county ? 'selected' : '' }}>{{ $county }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Thumbnail Image --}}
            <div class="mb-3">
                <label class="form-label">Thumbnail Image (Optional)</label>
                <input type="file" name="thumbnail_image" class="form-control">
            </div>

            {{-- Primary Product (File Upload or URL) --}}
            <div class="mb-3">
                <label class="form-label">Primary Product (Optional)</label>
                <input type="file" name="primary_product" class="form-control">
            </div>

            {{-- Primary Product URL (Optional) --}}
            <div class="mb-3">
                <label class="form-label">Primary Product URL (Optional)</label>
                <input type="url" name="primary_product_url" class="form-control" value="{{ old('primary_product_url', $project->primary_product_url) }}">
            </div>

            {{-- Secondary Product (File Upload or URL) --}}
            <div class="mb-3">
                <label class="form-label">Secondary Product (Optional)</label>
                <input type="file" name="secondary_product" class="form-control">
            </div>

            {{-- Secondary Product URL (Optional) --}}
            <div class="mb-3">
                <label class="form-label ">Secondary Product URL (Optional)</label>
                <input type="url" name="secondary_product_url" class="form-control" value="{{ old('secondary_product_url', $project->secondary_product_url) }}">
            </div>

            {{-- Categories --}}
            <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="border p-3 rounded">
                    @foreach($categories as $category)
                        <div class="form-check">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                   class="form-check-input"
                                   id="category_{{ $category->id }}"
                                   {{ in_array($category->id, old('categories', $project->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="category_{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn btn-success">Update Project</button>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
