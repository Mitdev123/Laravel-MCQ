
@extends('admin.layouts.app')

@section('title', 'Import Questions')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between mb-4">
        <h3>Excel / CSV Question Import</h3>

        <a href="{{ route('admin.questions.index') }}"
           class="btn btn-secondary">
            Back to Questions
        </a>
    </div>

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
            Upload Questions
        </div>

        <div class="card-body">

            <div class="alert alert-info">
                Upload an Excel or CSV file containing your questions.
                You can review all questions before importing them.
            </div>

            <a href="{{ route('admin.questions.import.template') }}"
               class="btn btn-success mb-4">
                Download Excel Template
            </a>

            <form action="{{ route('admin.questions.import.upload') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">

                    <label class="form-label">
                        Select Excel / CSV File
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".xlsx,.xls,.csv"
                           required>

                    <small class="text-muted">
                        Maximum file size: 10 MB.
                        Maximum 1000 question rows per batch.
                    </small>

                </div>

                <button type="submit" class="btn btn-primary">
                    Upload and Preview
                </button>

            </form>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header">
            Import History
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>File</th>
                        <th>Total</th>
                        <th>Valid</th>
                        <th>Invalid</th>
                        <th>Duplicates</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($batches as $batch)

                    <tr>

                        <td>{{ $batch->file_name }}</td>

                        <td>{{ $batch->total_rows }}</td>

                        <td class="text-success">
                            {{ $batch->valid_rows }}
                        </td>

                        <td class="text-danger">
                            {{ $batch->invalid_rows }}
                        </td>

                        <td>{{ $batch->duplicate_rows }}</td>

                        <td>
                            @if($batch->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($batch->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-secondary">Cancelled</span>
                            @endif
                        </td>

                        <td>
                            @if($batch->status === 'pending')
                                <a href="{{ route('admin.questions.import.preview', $batch) }}"
                                   class="btn btn-sm btn-primary">
                                    Preview
                                </a>
                            @else
                                <span class="text-muted">Processed</span>
                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No import history found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-body">
            {{ $batches->links() }}
        </div>

    </div>

</div>

@endsection
