
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3>Edit Subject</h3>

            <form action="{{ route('admin.subjects.update', $subject) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Subject Name</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $subject->name) }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>

                    <textarea name="description"
                              class="form-control">{{ old('description', $subject->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>

                    <select name="is_active" class="form-select">
                        <option value="1" @selected($subject->is_active)>Active</option>
                        <option value="0" @selected(!$subject->is_active)>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary">Update Subject</button>

            </form>

        </div>
    </div>
</div>
@endsection
