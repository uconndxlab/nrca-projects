@extends('layouts.app')
@section('title', 'Add New Project')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Project</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0" style="color: var(--text-dark);">Add New Project</h1>
            </div>
            <a href="{{ route('admin') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Admin
            </a>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title h6 mb-0 text-primary">
                        Project Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Assessing River Health Using Macroinvertebrates" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NRCA Program <span class="text-danger">*</span></label>
                            <input type="text" name="program" class="form-control" list="programList" value="{{ old('program') }}" placeholder="e.g. CAP" required>
                            <datalist id="programList">
                                @if(isset($programs) && count($programs) > 0)
                                    @foreach($programs as $p)
                                        <option value="{{ $p->name }}">{{ $p->full_name ?: $p->name }}</option>
                                    @endforeach
                                @else
                                    <option value="CAP">Conservation Ambassador Program (CAP)</option>
                                    <option value="CTP">Conservation Training Partnerships (CTP)</option>
                                    <option value="EDS">Environmental Data Science (EDS)</option>
                                @endif
                            </datalist>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cohort Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" min="2000" max="{{ date('Y') }}" value="{{ old('year', date('Y')) }}" required>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-2">
                            <span>Counties <span class="text-danger">*</span> <span class="text-muted fw-normal small">(Select all that apply)</span></span>
                        </label>
                        @php
                            $selectedCounties = old('counties', old('county') ? [old('county')] : []);
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
                        <input type="file" name="thumbnail_image" class="form-control" accept="image/*">
                        <div class="form-text">Recommended size: 800x450 (16:9 ratio). Max 2MB.</div>
                    </div>

                    {{-- Dynamic Media Products --}}
                    <div id="media-products-container">
                        {{-- Product #1 (Primary - At least one required) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light" id="media-product-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Primary Product <span class="text-danger">*</span></h6>
                                <span class="badge bg-secondary-subtle text-secondary small">Required</span>
                            </div>
                            <p class="text-muted small mb-2">Provide an uploaded file or website link.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload File</label>
                                    <input type="file" name="primary_product" id="primary_product_file" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Website Link</label>
                                    <input type="url" name="primary_product_url" id="primary_product_url" class="form-control form-control-sm" value="{{ old('primary_product_url') }}" placeholder="https://...">
                                </div>
                            </div>
                            <div id="primary-product-error" class="text-danger small mt-2 d-none">
                                <i class="bi bi-exclamation-circle me-1"></i>Please provide at least one media product (upload a file or website link).
                            </div>
                            @error('primary_product')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('primary_product_url')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Product #2 (Secondary - Optional) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ (old('secondary_product_url') || old('secondary_product')) ? '' : 'd-none' }}" id="media-product-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Secondary Product (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-2">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload File</label>
                                    <input type="file" name="secondary_product" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="secondary_product_url" class="form-control form-control-sm" value="{{ old('secondary_product_url') }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        {{-- Product #3 (Additional - Optional) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ (old('third_download_url') || old('third_download')) ? '' : 'd-none' }}" id="media-product-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #3 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-3">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload File</label>
                                    <input type="file" name="third_download" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="third_download_url" class="form-control form-control-sm" value="{{ old('third_download_url') }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        {{-- Product #4 (Additional - Optional) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ (old('fourth_download_url') || old('fourth_download')) ? '' : 'd-none' }}" id="media-product-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #4 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-4">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload File</label>
                                    <input type="file" name="fourth_download" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="fourth_download_url" class="form-control form-control-sm" value="{{ old('fourth_download_url') }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        {{-- Product #5 (Additional - Optional) --}}
                        <div class="media-product-item border rounded-3 p-3 mb-3 bg-light {{ (old('fifth_download_url') || old('fifth_download')) ? '' : 'd-none' }}" id="media-product-5">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Additional Deliverable #5 (Optional)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none remove-media-btn" data-target="#media-product-5">
                                    <i class="bi bi-x-circle me-1"></i>Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Upload File</label>
                                    <input type="file" name="fifth_download" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">OR Website Link</label>
                                    <input type="url" name="fifth_download_url" class="form-control form-control-sm" value="{{ old('fifth_download_url') }}" placeholder="https://...">
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

            {{-- 3. Categories / Topics --}}
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
                                           {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
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
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('admin') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    Save Project
                </button>
            </div>
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
            }
            updateAddButton();
        });
    }

    document.querySelectorAll('.remove-media-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.dataset.target);
            if (target) {
                target.querySelectorAll('input').forEach(input => input.value = '');
                target.classList.add('d-none');
                updateAddButton();
            }
        });
    });

    updateAddButton();

    // Client-side validation
    const form = document.querySelector('form[action="{{ route('projects.store') }}"]');
    const fileInput = document.getElementById('primary_product_file');
    const urlInput = document.getElementById('primary_product_url');
    const errorMsg = document.getElementById('primary-product-error');
    const countyError = document.getElementById('county-error');

    if (form) {
        form.addEventListener('submit', function (e) {
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
                const hasFile = fileInput.files && fileInput.files.length > 0;
                const hasUrl = urlInput.value.trim().length > 0;

                if (!hasFile && !hasUrl) {
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
