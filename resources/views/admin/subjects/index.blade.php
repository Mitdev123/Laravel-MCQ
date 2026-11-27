
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between mb-4">
        <h2>Manage Subjects</h2>

        <a href="{{ route('admin.subjects.create') }}"
           class="btn btn-primary">
            + Add Subject
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td>{{ $subject->id }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->slug }}</td>
                            <td>
                                {{ $subject->is_active ? 'Active' : 'Inactive' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.subjects.edit', $subject) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('admin.subjects.destroy', $subject) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this subject?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No subjects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $subjects->links() }}

        </div>
    </div>
</div>
@endsection
