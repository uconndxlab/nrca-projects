@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">Add Project</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('projects.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="program" class="form-control" placeholder="Filter by program..." value="{{ request('program') }}">
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select">
                        <option value="">Filter by year...</option>
                        @foreach(range(date('Y'), 1900) as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="county" class="form-control" placeholder="Filter by county..." value="{{ request('county') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success">Filter</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        {{-- Projects Grid --}}
        <div class="row">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        {{-- Thumbnail --}}
                        @if ($project->thumbnail)
                            <img src="{{ asset('storage/' . $project->thumbnail) }}" class="card-img-top" alt="{{ $project->title }}">
                        @else
                            <img src="https://via.placeholder.com/400x250?text=No+Image" class="card-img-top" alt="No Image">
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">{{ $project->title }}</h5>
                            <p class="card-text"><strong>Program:</strong> {{ $project->program ?? 'N/A' }}</p>
                            <p class="card-text"><strong>Year:</strong> {{ $project->year }}</p>
                            <p class="card-text"><strong>County:</strong> {{ $project->county }}</p>

                            {{-- Product Links --}}
                            <div class="mb-2">
                                @if ($project->primary_product)
                                    <a href="{{ asset('storage/' . $project->primary_product) }}" target="_blank" class="btn btn-sm btn-primary">Primary Product</a>
                                @endif
                                @if ($project->secondary_product)
                                    <a href="{{ asset('storage/' . $project->secondary_product) }}" target="_blank" class="btn btn-sm btn-secondary">Secondary Product</a>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $projects->links() }}
        </div>
    </div>
@endsection
