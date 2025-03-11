@extends('layouts.app')
@section('title', 'Projects')
@section('content')
    <div class="container">
        <h1 class="mb-4">Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">Add Project</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filter Form --}}
        <form hx-boost="true" hx-trigger="change, keyup delay:500ms" hx-target="#projects-container"
            hx-select="#projects-container" hx-swap="outerHTML" hx-push-url="true" method="GET"
            action="{{ route('projects.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col">
                    <input type="text" name="search" class="form-control" placeholder="Search for a topic..."
                        value="{{ request('search') }}">
                </div>
                <div class="col">
                    <select name="program" class="form-select">
                        <option value="">Filter by program...</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                {{ $program }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <select name="year" class="form-select">
                        <option value="">Filter by year...</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <select name="county" class="form-select">
                        <option value="">Filter by county...</option>
                        @foreach ($counties as $county)
                            <option value="{{ $county }}" {{ request('county') == $county ? 'selected' : '' }}>
                                {{ $county }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Projects Grid --}}
        <div id="projects-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach ($projects as $project)
                <div class="col">
                    <div class="card h-100">
                        {{-- Thumbnail --}}
                        <div class="position-relative">
                            @if ($project->thumbnail)
                                <img src="{{ asset('storage/' . $project->thumbnail) }}" class="card-img-top"
                                    alt="{{ $project->title }}">
                            @else
                                <img src="https://via.placeholder.com/400x250?text=No+Image" class="card-img-top"
                                    alt="No Image">
                            @endif

                            @if ($project->categories->isNotEmpty())
                                <div class="position-absolute top-0 start-0">
                                    @foreach ($project->categories as $category)
                                        <span class="badge bg-dark  m-2">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <h5 class="card-title position-absolute bottom-0 mb-0 start-0 end-0 bg-dark text-white p-2"
                                style="bottom: 0;">{{ $project->title }}</h5>


                        </div>
                        <div class="card-body">
                            {{-- Project Categories --}}

                            <p class="card-text"><strong>Program:</strong> {{ $project->program ?? 'N/A' }}</p>
                            <p class="card-text"><strong>Year:</strong> {{ $project->year }}</p>
                            <p class="card-text"><strong>County:</strong> {{ $project->county }}</p>
                        </div>

                        <div class="card-footer">
                            {{-- Product Links --}}
                            <div class="mb-2">
                                @if ($project->primary_product_url || $project->primary_product)
                                    @if ($project->primary_product_url)
                                        <a href="{{ $project->primary_product_url }}" target="_blank" class="btn btn-primary">
                                            <i class="bi bi-link-45deg"></i> View Project Website
                                        </a>
                                    @endif

                                    @if ($project->primary_product)
                                        <a href="{{ asset('storage/' . $project->primary_product) }}" target="_blank" class="btn btn-primary">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Download Poster
                                        </a>
                                    @endif
                                @endif

                                @if ($project->secondary_product_url || $project->secondary_product)
                                    @if ($project->secondary_product_url)
                                        <a href="{{ $project->secondary_product_url }}" target="_blank" class="btn btn-secondary">
                                            <i class="bi bi-link-45deg"></i> View Secondary Website
                                        </a>
                                    @endif

                                    @if ($project->secondary_product)
                                        <a href="{{ asset('storage/' . $project->secondary_product) }}" target="_blank" class="btn btn-secondary">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Download #2
                                        </a>
                                    @endif
                                @endif

                                @if ($project->third_product_url || $project->third_product)
                                    @if ($project->third_product_url)
                                        <a href="{{ $project->third_product_url }}" target="_blank" class="btn btn-secondary">
                                            <i class="bi bi-link-45deg"></i> View Third Website
                                        </a>
                                    @elseif ($project->third_product)
                                        <a href="{{ asset('storage/' . $project->third_product) }}" target="_blank" class="btn btn-secondary">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Download #3
                                        </a>
                                    @endif
                                @endif

                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($projects->isEmpty())
                <div class="col-12">
                    <div class="alert alert-info text-center">No projects found.</div>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $projects->links() }}
        </div>
    </div>
@endsection
