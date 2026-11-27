
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Add New Subject</h3>

            <form action="{{ route('admin.subjects.store') }}"
                  method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Subject Name</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>

                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>




    <div class="mb-3">
        <label>Category</label>

        <select name="category_id" class="form-control" required>
            <option value="">Select Category</option>

            @foreach($categories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        @error('category_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-3">
        <label>Subject Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name') }}" required>
    </div>


                <div class="mb-3">
                    <label class="form-label">Description</label>

                    <textarea name="description"
                              class="form-control">{{ old('description') }}</textarea>
                </div>

                <button class="btn btn-success">Save Subject</button>

                <a href="{{ route('admin.subjects.index') }}"
                   class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>
</div>
@endsection
