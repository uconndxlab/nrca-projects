@extends('layouts.app')
@section('title', 'Edit Project')
@section('content')
    <div class="container">
        <h1 class="mb-4 text-center">Edit Project</h1>

        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" class="shadow p-4 rounded bg-light">
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
                    @foreach ($counties as $county)
                        <option value="{{ $county }}" {{ old('county', $project->county) == $county ? 'selected' : '' }}>{{ $county }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Thumbnail Image --}}
            <div class="mb-3">
                <label class="form-label">Thumbnail Image (Optional)</label>
                <input type="file" name="thumbnail_image" class="form-control">
                @if ($project->thumbnail)
                    <img src="{{ asset('storage/' . $project->thumbnail) }}" class="img-fluid mt-2" alt="{{ $project->title }}">
                @endif
            </div>

            {{-- Primary Product --}}
            <div class="mb-3">
                <label class="form-label">Primary Product (Optional)</label>
                <input type="file" name="primary_product" class="form-control">
                @if ($project->primary_product)
                    <small>Current file: {{ $project->primary_product }}</small>
                @endif
            </div>

            {{-- Primary Product URL (Optional) --}}
            <div class="mb-3">
                <label class="form-label">Primary Product URL (Optional)</label>
                <input type="url" name="primary_product_url" class="form-control" value="{{ old('primary_product_url', $project->primary_product_url) }}">
            </div>

            {{-- Secondary Product --}}
            <div class="mb-3">
                <label class="form-label">Secondary Product (Optional)</label>
                <input type="file" name="secondary_product" class="form-control">
                @if ($project->secondary_product)
                    <small>Current file: {{ $project->secondary_product }}</small>
                @endif
            </div>

            {{-- Secondary Product URL (Optional) --}}
            <div class="mb-3">
                <label class="form-label">Secondary Product URL (Optional)</label>
                <input type="url" name="secondary_product_url" class="form-control" value="{{ old('secondary_product_url', $project->secondary_product_url) }}">
            </div>

            {{-- Third Product --}}
            <div class="mb-3">
                <label class="form-label">Third Product (Optional)</label>
                <input type="file" name="third_download" class="form-control">
                @if ($project->third_download)
                    <small>Current file: {{ $project->third_download }}</small>
                @endif
            </div>

            {{-- Third Product URL (Optional) --}}
            <div class="mb-3">
                <label class="form-label">Third Product URL (Optional)</label>
                <input type="url" name="third_download_url" class="form-control" value="{{ old('third_download_url', $project->third_download_url) }}">
            </div>

            {{-- Categories --}}
            <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="border p-3 rounded bg-white">
                    @foreach ($categories as $category)
                        <div class="form-check">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="form-check-input" id="category_{{ $category->id }}" {{ in_array($category->id, old('categories', $project->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="category_{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">Update Project</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
