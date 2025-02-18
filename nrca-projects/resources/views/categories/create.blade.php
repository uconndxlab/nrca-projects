@extends('layouts.app')

@section('content')
    <h1>Add Category</h1>
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input id="name" type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>

<script>
    // auto focus on the first input field
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#name').focus();
    });
</script>
@endsection
