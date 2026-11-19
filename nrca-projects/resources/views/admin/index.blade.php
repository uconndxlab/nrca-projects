@extends('layouts.app')
@section('title', 'Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1" style="color: var(--text-dark);">Admin</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">← Back to Browse</a>
        <a href="{{ route('projects.create') }}" class="btn btn-nrca-orange btn-sm">Add Project</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Admin Tabs --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'projects' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'projects']) }}">
                    Projects ({{ $stats['total_projects'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'programs' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'programs']) }}">
                    Programs ({{ $stats['total_programs'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'categories' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'categories']) }}">
                    Topics ({{ $stats['total_categories'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'years' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'years']) }}">
                    Years ({{ $stats['total_years'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'counties' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'counties']) }}">
                    Counties ({{ $stats['total_counties'] }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'users' ? 'active fw-bold' : 'text-dark' }}" href="{{ route('admin', ['tab' => 'users']) }}">
                    Staff Users ({{ $users->count() }})
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-3">
        @if($activeTab === 'projects')

            @if($projectsByYear->isEmpty())
                <div class="alert alert-light border text-center py-4 mb-0 text-muted">
                    No projects found matching the criteria.
                </div>
            @else
                <div class="accordion" id="projectsYearAccordion">
                    @foreach($projectsByYear as $year => $yearProjects)
                        @php
                            $collapseId = 'collapseYear_' . Str::slug($year);
                            $isFirst = $loop->first;
                        @endphp
                        <div class="accordion-item mb-2 border rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="heading_{{ $collapseId }}">
                                <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }} py-2 px-3 bg-light fw-bold text-dark"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                        aria-controls="{{ $collapseId }}">
                                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                        <span>
                                            <!-- <i class="bi bi-calendar-event me-2 text-primary"></i> -->
                                            {{ $year }}
                                        </span>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill fw-normal px-2 py-1">
                                            {{ $yearProjects->count() }} {{ Str::plural('project', $yearProjects->count()) }}
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="{{ $collapseId }}"
                                 class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                 aria-labelledby="heading_{{ $collapseId }}"
                                 data-bs-parent="#projectsYearAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light small text-muted border-bottom">
                                                <tr>
                                                    <th class="ps-3 py-2" style="width: 48%;">Title</th>
                                                    <th style="width: 14%;">Program</th>
                                                    <th style="width: 22%;">Counties</th>
                                                    <th class="text-end pe-3" style="width: 16%;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($yearProjects as $project)
                                                    <tr>
                                                        <td class="ps-3 py-2">
                                                            @php
                                                                $websiteUrl = $project->primary_product_url ?: ($project->secondary_product_url ?: $project->third_download_url);
                                                            @endphp
                                                            @if($websiteUrl)
                                                                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-dark text-decoration-none hover-underline" title="View external media">
                                                                    {{ $project->title }}
                                                                    <i class="bi bi-box-arrow-up-right small text-muted ms-1" style="font-size: 0.75rem;"></i>
                                                                </a>
                                                            @else
                                                                <span class="fw-semibold text-dark">{{ $project->title }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($project->program)
                                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">{{ $project->program }}</span>
                                                            @else
                                                                <span class="text-muted small">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="small text-muted">
                                                            {{ $project->county ?: '—' }}
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit project">
                                                                    Edit
                                                                </a>
                                                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete project \'{{ addslashes($project->title) }}\'?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete project">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggleBtn = document.getElementById('toggleAllYearsBtn');
                if (!toggleBtn) return;
                let allExpanded = false;
                toggleBtn.addEventListener('click', function () {
                    const collapses = document.querySelectorAll('#projectsYearAccordion .accordion-collapse');
                    allExpanded = !allExpanded;
                    collapses.forEach(el => {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                        if (allExpanded) {
                            bsCollapse.show();
                        } else {
                            bsCollapse.hide();
                        }
                    });
                    toggleBtn.innerHTML = allExpanded
                        ? '<i class="bi bi-arrows-collapse me-1"></i>Collapse All'
                        : '<i class="bi bi-arrows-expand me-1"></i>Expand All';
                });
            });
            </script>

        @elseif($activeTab === 'programs')
            <div class="card bg-light border-0 mb-4 p-3">
                <h6 class="fw-bold mb-2">Add New Program</h6>
                <form method="POST" action="{{ route('admin.programs.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Code / Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. CAP" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Full Name (Optional)</label>
                        <input type="text" name="full_name" class="form-control form-control-sm" placeholder="e.g. Conservation Ambassador Program">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Description (Optional)</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Brief description...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add Program</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Program Code</th>
                            <th>Full Name</th>
                            <th>Description</th>
                            <th class="text-center">Projects</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $prog)
                            <tr>
                                <td class="fw-bold">
                                    <span class="badge bg-secondary">{{ $prog->name }}</span>
                                </td>
                                <td>{{ $prog->full_name ?: '-' }}</td>
                                <td class="text-muted small">{{ $prog->description ?: '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('projects.index', ['program' => $prog->name]) }}" class="badge bg-secondary text-decoration-none">
                                        {{ $prog->projects_count }} projects
                                    </a>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editProgModal{{ $prog->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.programs.destroy', $prog) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete program \'{{ $prog->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>

                                    {{-- Edit Modal --}}
                                    <div class="modal fade" id="editProgModal{{ $prog->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.programs.update', $prog) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Edit Program: {{ $prog->name }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label small">Program Code</label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $prog->name }}" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small">Full Name</label>
                                                            <input type="text" name="full_name" class="form-control form-control-sm" value="{{ $prog->full_name }}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small">Description</label>
                                                            <textarea name="description" class="form-control form-control-sm" rows="2">{{ $prog->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-1">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No programs configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'categories')
            <div class="card bg-light border-0 mb-4 p-3">
                <h6 class="fw-bold mb-2">Add New Topic</h6>
                <form method="POST" action="{{ route('categories.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Topic Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Wildlife" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Description (Optional)</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Short description...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add Topic</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Topic Name</th>
                            <th>Description</th>
                            <th class="text-center">Projects</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td class="fw-bold">{{ $cat->name }}</td>
                                <td class="text-muted small">{{ $cat->description ?: '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('projects.index', ['category' => $cat->id]) }}" class="badge bg-secondary text-decoration-none">
                                        {{ $cat->projects_count }} projects
                                    </a>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editCatModal{{ $cat->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete topic \'{{ $cat->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>

                                    {{-- Edit Modal --}}
                                    <div class="modal fade" id="editCatModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('categories.update', $cat) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Edit Topic: {{ $cat->name }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label small">Topic Name</label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small">Description</label>
                                                            <textarea name="description" class="form-control form-control-sm" rows="2">{{ $cat->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-1">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No topics created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'years')
            <div class="card bg-light border-0 mb-4 p-3">
                <h6 class="fw-bold mb-2">Add Cohort Year</h6>
                <form method="POST" action="{{ route('admin.years.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Year <span class="text-danger">*</span></label>
                        <input type="number" name="year" class="form-control form-control-sm" placeholder="e.g. 2025" min="1900" max="2100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Notes / Description (Optional)</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. 2025 Cohort">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add Year</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Year</th>
                            <th>Notes</th>
                            <th class="text-center">Projects</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($years as $yr)
                            <tr>
                                <td class="fw-bold">{{ $yr->year }}</td>
                                <td class="text-muted small">{{ $yr->notes ?: '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('projects.index', ['year' => $yr->year]) }}" class="badge bg-secondary text-decoration-none">
                                        {{ $yr->projects_count }} projects
                                    </a>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editYearModal{{ $yr->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.years.destroy', $yr) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete year {{ $yr->year }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>

                                    {{-- Edit Modal --}}
                                    <div class="modal fade" id="editYearModal{{ $yr->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.years.update', $yr) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Edit Year: {{ $yr->year }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label small">Year</label>
                                                            <input type="number" name="year" class="form-control form-control-sm" value="{{ $yr->year }}" min="1900" max="2100" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small">Notes</label>
                                                            <input type="text" name="notes" class="form-control form-control-sm" value="{{ $yr->notes }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-1">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No cohort years configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'counties')
            <div class="card bg-light border-0 mb-4 p-3">
                <h6 class="fw-bold mb-2">Add County</h6>
                <form method="POST" action="{{ route('admin.counties.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-9">
                        <label class="form-label small mb-1">County Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Tolland or Statewide" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add County</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>County</th>
                            <th class="text-center">Projects</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($managedCounties as $c)
                            <tr>
                                <td class="fw-bold">{{ $c->name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('projects.index', ['county' => $c->name]) }}" class="badge bg-secondary text-decoration-none">
                                        {{ $c->projects_count }} projects
                                    </a>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editCountyModal{{ $c->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.counties.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete county \'{{ $c->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>

                                    {{-- Edit Modal --}}
                                    <div class="modal fade" id="editCountyModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.counties.update', $c) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Edit County: {{ $c->name }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label small">County Name</label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $c->name }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-1">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No counties configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'users')
            <div class="card bg-light border-0 mb-4 p-3">
                <h6 class="fw-bold mb-2">Authorize Staff User</h6>
                <form method="POST" action="{{ route('admin.users.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small mb-1">NetID <span class="text-danger">*</span></label>
                        <input type="text" name="netid" class="form-control form-control-sm" placeholder="e.g. abc12345" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Name (Optional)</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Full name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Email (Optional)</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="first.last@uconn.edu">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check pt-3">
                            <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="isAdminCheck" checked>
                            <label class="form-check-label small" for="isAdminCheck">Admin</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>NetID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <code>{{ $user->netid }}</code>
                                    @if($user->id === Auth::id())
                                        <span class="badge bg-light text-dark border ms-1">You</span>
                                    @endif
                                </td>
                                <td>{{ $user->name ?: '-' }}</td>
                                <td>{{ $user->email ?: '-' }}</td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-success">Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Staff</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                                {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Remove user {{ $user->netid }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-muted small fst-italic">Current User</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
