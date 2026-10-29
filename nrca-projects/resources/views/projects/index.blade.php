@extends('layouts.app')
@section('title', 'Projects')

@section('content')
    {{-- Top Summary & Admin Controls --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <p class="text-muted small mb-0">Showing {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} projects</p>
        </div>
        @can('admin')
            <div class="d-flex align-items-center gap-2 standalone-only">
                <a href="{{ route('projects.create') }}" class="btn btn-sm btn-nrca-orange" hx-boost="false">
                    Add Project
                </a>
                <a href="{{ route('admin') }}" class="btn btn-sm btn-nrca-orange" hx-boost="false">
                    Manage
                </a>
            </div>
        @endcan
    </div>

    {{-- Dynamic Filter & Showcase Container --}}
    <div id="projects-container" hx-boost="true" hx-target="#projects-container" hx-select="#projects-container" hx-swap="outerHTML" hx-push-url="true">
        {{-- Filter Bars with Right-Aligned Minimal Pagination on the Same Row --}}
        <div class="filter-toolbar mb-4">
            <form hx-boost="true" hx-trigger="change, keyup delay:500ms from:#search-input" hx-target="#projects-container"
                hx-select="#projects-container" hx-swap="outerHTML" hx-push-url="true" method="GET"
                action="{{ route('projects.index') }}" class="m-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    {{-- Filter Dropdowns --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div style="width: 155px;">
                            <input type="text" id="search-input" name="search" class="form-control form-control-sm"
                                placeholder="Search..." value="{{ request('search') }}">
                        </div>
                        <div style="width: 122px;">
                            <select name="program" class="form-select form-select-sm filter-bar-select">
                                <option value="">All Programs</option>
                                @foreach ($programs as $program)
                                    @if($program)
                                        <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                            {{ $program }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 108px;">
                            <select name="category" class="form-select form-select-sm filter-bar-select">
                                <option value="">All Topics</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 86px;">
                            <select name="year" class="form-select form-select-sm filter-bar-select">
                                <option value="">All Years</option>
                                @foreach ($years as $year)
                                    @if($year)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 108px;">
                            <select name="county" class="form-select form-select-sm filter-bar-select">
                                <option value="">All Counties</option>
                                @foreach ($counties as $county)
                                    @if($county)
                                        <option value="{{ $county }}" {{ request('county') == $county ? 'selected' : '' }}>
                                            {{ $county }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @if(request()->hasAny(['search', 'program', 'category', 'year', 'county']))
                            <div>
                                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-nowrap" style="font-size: 0.8rem;">Reset</a>
                            </div>
                        @endif
                    </div>

                    {{-- Compact Minimal Pagination to the right of filter bars --}}
                    <div class="d-flex align-items-center ms-auto">
                        {{ $projects->withQueryString()->onEachSide(1)->links('partials.pagination') }}
                    </div>
                </div>
            </form>
        </div>

        {{-- Projects Grid --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
            @forelse ($projects as $project)
                <div class="col">
                    <div class="card project-card h-100">
                        {{-- Thumbnail --}}
                        <div class="position-relative">
                            @if ($project->thumbnail)
                                <img src="{{ asset('storage/' . $project->thumbnail) }}" class="card-img-top"
                                    alt="{{ $project->title }}" style="height: 180px; object-fit: cover;">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light text-muted" style="height: 180px;">
                                    <small class="text-secondary"><i class="bi bi-image me-1"></i> No Preview Available</small>
                                </div>
                            @endif

                            @if ($project->categories->isNotEmpty())
                                <div class="position-absolute top-0 start-0 m-2 d-flex flex-wrap gap-1">
                                    @foreach ($project->categories->take(3) as $category)
                                        <span class="badge-tag">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">{{ $project->title }}</h5>
                            <div class="mt-auto pt-2">
                                <p class="card-text mb-1"><strong>Program:</strong> {{ $project->program ?? 'N/A' }}</p>
                                <p class="card-text mb-1"><strong>Year:</strong> {{ $project->year }}</p>
                                <p class="card-text mb-0"><strong>{{ str_contains($project->county ?? '', ',') ? 'Counties:' : 'County:' }}</strong> {{ $project->county }}</p>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                            {{-- Primary Action Button --}}
                            @if ($project->primary_product_url)
                                <a href="{{ $project->primary_product_url }}" target="_blank"
                                    class="btn btn-primary btn-sm w-100 mb-2">
                                    View Project Website <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                            @elseif ($project->primary_product)
                                <a href="{{ asset('storage/' . $project->primary_product) }}" target="_blank"
                                    class="btn btn-primary btn-sm w-100 mb-2">
                                    Download Poster <i class="bi bi-download ms-1 small"></i>
                                </a>
                            @endif

                            {{-- Secondary Deliverable Links --}}
                            <div class="d-flex flex-column gap-1">
                                @if ($project->primary_product_url && $project->primary_product)
                                    <a href="{{ asset('storage/' . $project->primary_product) }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Poster PDF
                                    </a>
                                @endif
                                @if ($project->secondary_product_url)
                                    <a href="{{ $project->secondary_product_url }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-link-45deg me-1"></i> Secondary Website
                                    </a>
                                @elseif ($project->secondary_product)
                                    <a href="{{ asset('storage/' . $project->secondary_product) }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download #2
                                    </a>
                                @endif
                                @if ($project->third_download_url)
                                    <a href="{{ $project->third_download_url }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-link-45deg me-1"></i> Additional Link
                                    </a>
                                @elseif ($project->third_download)
                                    <a href="{{ asset('storage/' . $project->third_download) }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download #3
                                    </a>
                                @endif
                                @if ($project->fourth_download_url)
                                    <a href="{{ $project->fourth_download_url }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-link-45deg me-1"></i> Deliverable #4
                                    </a>
                                @elseif ($project->fourth_download)
                                    <a href="{{ asset('storage/' . $project->fourth_download) }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download #4
                                    </a>
                                @endif
                                @if ($project->fifth_download_url)
                                    <a href="{{ $project->fifth_download_url }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-link-45deg me-1"></i> Deliverable #5
                                    </a>
                                @elseif ($project->fifth_download)
                                    <a href="{{ asset('storage/' . $project->fifth_download) }}" target="_blank" class="deliverable-link">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download #5
                                    </a>
                                @endif
                            </div>

                            @can('admin')
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top small standalone-only">
                                    <a href="{{ route('projects.edit', $project) }}" class="text-decoration-none text-muted" hx-boost="false"><i class="bi bi-pencil me-1"></i>Edit</a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this project?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0 text-decoration-none"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center py-5">
                        <p class="text-muted mb-2">No projects match the selected criteria.</p>
                        @if(request()->hasAny(['search', 'program', 'category', 'year', 'county']))
                            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary">Clear all filters</a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Bottom Minimal Pagination --}}
        @if ($projects->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 mb-4 pt-3 border-top">
                <span class="text-muted small">Showing {{ $projects->firstItem() }}–{{ $projects->lastItem() }} of {{ $projects->total() }} projects</span>
                <div>
                    {{ $projects->withQueryString()->onEachSide(1)->links('partials.pagination') }}
                </div>
            </div>
        @endif
    </div>
@endsection
