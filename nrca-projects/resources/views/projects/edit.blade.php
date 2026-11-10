@extends('layouts.app')
@section('title', 'Edit Project: ' . $project->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Project</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0" style="color: var(--text-dark);">Edit Project</h1>
            </div>
            <a href="{{ route('admin') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Admin
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <strong>Please correct the following errors:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('projects.update', $project) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 1. Project Information --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title h6 mb-0 text-primary">
                        Project Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $project->title) }}" placeholder="e.g. Assessing River Health Using Macroinvertebrates" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NRCA Program <span class="text-danger">*</span></label>
                            <select name="program" class="form-select @error('program') is-invalid @enderror" required>
                                <option value="">Select a Program</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->name }}" {{ old('program', $project->program) === $p->name ? 'selected' : '' }}>
                                        {{ $p->name }} — {{ $p->full_name ?: $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cohort Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" min="2000" max="{{ date('Y') + 1 }}" value="{{ old('year', $project->year) }}" placeholder="e.g. {{ date('Y') }}" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-2">
                            <span>Counties <span class="text-danger">*</span> <span class="text-muted fw-normal small">(Select all that apply)</span></span>
                        </label>
                        @php
                            $projectCounties = array_map('trim', explode(',', $project->county ?? ''));
                            $normalizedProjectCounties = array_map(function($c) {
                                $lower = strtolower(str_replace(' ', '', $c));
                                if ($lower === 'newhaven') return 'New Haven';
                                if ($lower === 'newlondon') return 'New London';
                                if ($lower === 'harford') return 'Hartford';
                                return ucwords($c);
                            }, $projectCounties);
                            $selectedCounties = old('counties', old('county') ? [old('county')] : $normalizedProjectCounties);
                        @endphp
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2">
                                @foreach($counties as $c)
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input county-checkbox" type="checkbox" name="counties[]" value="{{ $c }}" id="county_{{ Str::slug($c) }}"
                                                {{ in_array($c, $selectedCounties) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="county_{{ Str::slug($c) }}">
                                                {{ $c }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div id="county-error" class="text-danger small mt-1 d-none">
                            <i class="bi bi-exclamation-circle me-1"></i>Please select at least one county.
                        </div>
                        @error('counties')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('county')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 2. Media --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title h6 mb-0 text-primary">
                        Media
                    </h5>
                </div>
                <div class="card-body p-4">
                    {{-- Thumbnail --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Thumbnail Image (Optional)</label>
                        @if ($project->thumbnail)
                            <div class="d-flex align-items-center gap-3 mb-2 p-2 bg-light rounded">
                                <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="" class="rounded shadow-sm" style="width: 120px; height: 70px; object-fit: cover;">
                                <div>
                                    <small class="text-success d-block fw-bold"><i class="bi bi-check-circle me-1"></i> Current thumbnail uploaded</small>
                                    <small class="text-muted">Upload a new file below to replace it.</small>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="thumbnail_image" class="form-control @error('thumbnail_image') is-invalid @enderror" accept="image/*">
                        <div class="form-text">Recommended size: 800x450 (16:9 ratio). Max 10MB.</div>
                        @error('thumbnail_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Dynamic Media Products --}}
                    <div id="media-products-container">
                        {{-- Product #1 (Primary - At least one required) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light" id="media-product-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Primary Product <span class="text-danger">*</span></h6>
                                <span class="badge bg-secondary-subtle text-secondary small">Required</span>
                            </div>
                            @if($project->primary_product)
                                <div class="mb-2 small existing-file-badge">
                                    <a href="{{ asset('storage/' . $project->primary_product) }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> View Current File
                                    </a>
                                </div>
                            @endif
                            <p class="text-muted small mb-2">Provide an uploaded file (max 25MB) or website link.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $project->primary_product ? 'Replace File' : 'Upload File' }}</label>
                                    <input type="file" name="primary_product" id="primary_product_file" class="form-control form-control-sm @error('primary_product') is-invalid @enderror">
                                    @error('primary_product')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Website Link</label>
                                    <input type="url" name="primary_product_url" id="primary_product_url" class="form-control form-control-sm @error('primary_product_url') is-invalid @enderror" value="{{ old('primary_product_url', $project->primary_product_url) }}" placeholder="https://...">
                                    @error('primary_product_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="primary-product-error" class="text-danger small mt-2 d-none">
                                <i class="bi bi-exclamation-circle me-1"></i>Please provide at least one media product (upload a file or website link).
                            </div>
                        </div>

                        {{-- Product #2 (Secondary - Optional) --}}
                        @php
                            $hasMedia2 = old('secondary_product_url') || old('secondary_product') || $project->secondary_product || $project->secondary_product_url || $errors->has('secondary_product') || $errors->has('secondary_product_url');
                        @endphp
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ $hasMedia2 ? '' : 'd-none' }}" id="media-product-2">
                            <input type="hidden" name="remove_secondary_product" id="remove_secondary_product" value="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Secondary Product (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-2" data-remove-input="#remove_secondary_product">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            @if($project->secondary_product)
                                <div class="mb-2 small existing-file-badge">
                                    <a href="{{ asset('storage/' . $project->secondary_product) }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-file-earmark-arrow-down-fill text-primary me-1"></i> View Current File
                                    </a>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $project->secondary_product ? 'Replace File' : 'Upload File' }}</label>
                                    <input type="file" name="secondary_product" class="form-control form-control-sm @error('secondary_product') is-invalid @enderror">
                                    <div class="form-text small">Max 25MB.</div>
                                    @error('secondary_product')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="secondary_product_url" class="form-control form-control-sm @error('secondary_product_url') is-invalid @enderror" value="{{ old('secondary_product_url', $project->secondary_product_url) }}" placeholder="https://...">
                                    @error('secondary_product_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Product #3 (Additional - Optional) --}}
                        @php
                            $hasMedia3 = old('third_download_url') || old('third_download') || $project->third_download || $project->third_download_url || $errors->has('third_download') || $errors->has('third_download_url');
                        @endphp
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ $hasMedia3 ? '' : 'd-none' }}" id="media-product-3">
                            <input type="hidden" name="remove_third_download" id="remove_third_download" value="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #3 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-3" data-remove-input="#remove_third_download">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            @if($project->third_download)
                                <div class="mb-2 small existing-file-badge">
                                    <a href="{{ asset('storage/' . $project->third_download) }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-paperclip text-muted me-1"></i> View Current File
                                    </a>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $project->third_download ? 'Replace File' : 'Upload File' }}</label>
                                    <input type="file" name="third_download" class="form-control form-control-sm @error('third_download') is-invalid @enderror">
                                    <div class="form-text small">Max 25MB.</div>
                                    @error('third_download')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="third_download_url" class="form-control form-control-sm @error('third_download_url') is-invalid @enderror" value="{{ old('third_download_url', $project->third_download_url) }}" placeholder="https://...">
                                    @error('third_download_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Product #4 (Additional - Optional) --}}
                        @php
                            $hasMedia4 = old('fourth_download_url') || old('fourth_download') || $project->fourth_download || $project->fourth_download_url || $errors->has('fourth_download') || $errors->has('fourth_download_url');
                        @endphp
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ $hasMedia4 ? '' : 'd-none' }}" id="media-product-4">
                            <input type="hidden" name="remove_fourth_download" id="remove_fourth_download" value="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #4 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-4" data-remove-input="#remove_fourth_download">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            @if($project->fourth_download)
                                <div class="mb-2 small existing-file-badge">
                                    <a href="{{ asset('storage/' . $project->fourth_download) }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-paperclip text-muted me-1"></i> View Current File
                                    </a>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $project->fourth_download ? 'Replace File' : 'Upload File' }}</label>
                                    <input type="file" name="fourth_download" class="form-control form-control-sm @error('fourth_download') is-invalid @enderror">
                                    <div class="form-text small">Max 25MB.</div>
                                    @error('fourth_download')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="fourth_download_url" class="form-control form-control-sm @error('fourth_download_url') is-invalid @enderror" value="{{ old('fourth_download_url', $project->fourth_download_url) }}" placeholder="https://...">
                                    @error('fourth_download_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Product #5 (Additional - Optional) --}}
                        @php
                            $hasMedia5 = old('fifth_download_url') || old('fifth_download') || $project->fifth_download || $project->fifth_download_url || $errors->has('fifth_download') || $errors->has('fifth_download_url');
                        @endphp
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ $hasMedia5 ? '' : 'd-none' }}" id="media-product-5">
                            <input type="hidden" name="remove_fifth_download" id="remove_fifth_download" value="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #5 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-5" data-remove-input="#remove_fifth_download">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            @if($project->fifth_download)
                                <div class="mb-2 small existing-file-badge">
                                    <a href="{{ asset('storage/' . $project->fifth_download) }}" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-paperclip text-muted me-1"></i> View Current File
                                    </a>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">{{ $project->fifth_download ? 'Replace File' : 'Upload File' }}</label>
                                    <input type="file" name="fifth_download" class="form-control form-control-sm @error('fifth_download') is-invalid @enderror">
                                    <div class="form-text small">Max 25MB.</div>
                                    @error('fifth_download')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="fifth_download_url" class="form-control form-control-sm @error('fifth_download_url') is-invalid @enderror" value="{{ old('fifth_download_url', $project->fifth_download_url) }}" placeholder="https://...">
                                    @error('fifth_download_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" id="add-media-btn" class="btn btn-sm btn-outline-secondary">
                            Add additional media
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. Associated Tags --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title h6 mb-0 text-primary">
                        Associated Tags
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row row-cols-2 row-cols-md-3 g-2">
                        @foreach($categories as $category)
                            <div class="col">
                                <div class="form-check">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           class="form-check-input"
                                           id="cat_{{ $category->id }}"
                                           {{ in_array($category->id, old('categories', $project->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="cat_{{ $category->id }}">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-between align-items-center mb-5">
                <!-- <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Are you sure you want to delete this project?')) { document.getElementById('deleteProjectForm').submit(); }">
                    <i class="bi bi-trash me-1"></i> Delete Project
                </button> -->
                <div class="d-flex gap-2">
                    <a href="{{ route('admin') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        Save Project
                    </button>
                </div>
            </div>
        </form>

        {{-- Hidden Delete Form --}}
        <form id="deleteProjectForm" action="{{ route('projects.destroy', $project) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addMediaBtn = document.getElementById('add-media-btn');
    const optionalProds = [
        document.getElementById('media-product-2'),
        document.getElementById('media-product-3'),
        document.getElementById('media-product-4'),
        document.getElementById('media-product-5')
    ];

    function updateAddButton() {
        const hasHidden = optionalProds.some(el => el && el.classList.contains('d-none'));
        if (addMediaBtn) {
            addMediaBtn.style.display = hasHidden ? 'inline-block' : 'none';
        }
    }

    if (addMediaBtn) {
        addMediaBtn.addEventListener('click', function () {
            const nextHidden = optionalProds.find(el => el && el.classList.contains('d-none'));
            if (nextHidden) {
                nextHidden.classList.remove('d-none');
                const removeInput = nextHidden.querySelector('input[type="hidden"][name^="remove_"]');
                if (removeInput) {
                    removeInput.value = '0';
                }
            }
            updateAddButton();
        });
    }

    document.querySelectorAll('.remove-media-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.dataset.target);
            if (target) {
                target.querySelectorAll('input:not([type="hidden"])').forEach(input => input.value = '');
                const removeInput = document.querySelector(this.dataset.removeInput);
                if (removeInput) {
                    removeInput.value = '1';
                }
                const badge = target.querySelector('.existing-file-badge');
                if (badge) {
                    badge.classList.add('d-none');
                }
                target.classList.add('d-none');
                updateAddButton();
            }
        });
    });

    updateAddButton();

    // File size validation helper
    function setupFileSizeValidation(inputSelector, maxMb, errorText) {
        const input = document.querySelector(inputSelector);
        if (!input) return;
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const maxBytes = maxMb * 1024 * 1024;
            let feedback = this.parentNode.querySelector('.client-file-error');
            if (file && file.size > maxBytes) {
                this.classList.add('is-invalid');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback client-file-error';
                    this.parentNode.appendChild(feedback);
                }
                feedback.textContent = errorText;
                feedback.style.display = 'block';
            } else {
                if (feedback) {
                    feedback.remove();
                }
                this.classList.remove('is-invalid');
            }
        });
    }

    setupFileSizeValidation('input[name="thumbnail_image"]', 10, 'Thumbnail must be under 10MB.');
    setupFileSizeValidation('input[name="primary_product"]', 25, 'Primary file must be under 25MB.');
    setupFileSizeValidation('input[name="secondary_product"]', 25, 'Secondary file must be under 25MB.');
    setupFileSizeValidation('input[name="third_download"]', 25, 'Deliverable #3 file must be under 25MB.');
    setupFileSizeValidation('input[name="fourth_download"]', 25, 'Deliverable #4 file must be under 25MB.');
    setupFileSizeValidation('input[name="fifth_download"]', 25, 'Deliverable #5 file must be under 25MB.');

    // Client-side validation
    const form = document.querySelector('form[action="{{ route('projects.update', $project) }}"]');
    const fileInput = document.getElementById('primary_product_file');
    const urlInput = document.getElementById('primary_product_url');
    const errorMsg = document.getElementById('primary-product-error');
    const countyError = document.getElementById('county-error');
    const hasExistingFile = {{ $project->primary_product ? 'true' : 'false' }};

    if (form) {
        form.addEventListener('submit', function (e) {
            // Check for client-side file size errors
            const oversizeInputs = form.querySelectorAll('.client-file-error');
            if (oversizeInputs.length > 0) {
                e.preventDefault();
                alert('Please remove or replace files exceeding the maximum size limit before saving.');
                return;
            }
            const countyChecked = document.querySelectorAll('.county-checkbox:checked').length > 0;
            if (!countyChecked) {
                e.preventDefault();
                if (countyError) countyError.classList.remove('d-none');
                const firstCb = document.querySelector('.county-checkbox');
                if (firstCb) firstCb.focus();
                return;
            } else if (countyError) {
                countyError.classList.add('d-none');
            }

            if (fileInput && urlInput && errorMsg) {
                const hasNewFile = fileInput.files && fileInput.files.length > 0;
                const hasUrl = urlInput.value.trim().length > 0;

                if (!hasExistingFile && !hasNewFile && !hasUrl) {
                    e.preventDefault();
                    errorMsg.classList.remove('d-none');
                    urlInput.focus();
                } else {
                    errorMsg.classList.add('d-none');
                }
            }
        });

        document.querySelectorAll('.county-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {
                if (countyError && document.querySelectorAll('.county-checkbox:checked').length > 0) {
                    countyError.classList.add('d-none');
                }
            });
        });

        if (fileInput && errorMsg) {
            fileInput.addEventListener('change', function () {
                if (fileInput.files.length > 0) errorMsg.classList.add('d-none');
            });
        }
        if (urlInput && errorMsg) {
            urlInput.addEventListener('input', function () {
                if (urlInput.value.trim().length > 0) errorMsg.classList.add('d-none');
            });
        }
    }
});
</script>
@endsection
