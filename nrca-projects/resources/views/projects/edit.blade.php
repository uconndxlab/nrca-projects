@extends('layouts.app')

@section('content')
    <h1>Edit Project</h1>
    <form action="{{ route('projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="project_title" class="form-control" value="{{ $project->project_title }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Program</label>
            <input type="text" name="program" class="form-control" value="{{ $project->program }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
@endsection
