
@extends('layouts.app')

@section('heading', 'Add New Question')

@section('content')

<div class="card p-4">

    <form method="POST" action="{{ route('admin.questions.store') }}">
        @csrf

        {{-- Validation Error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Subject --}}
        <label class="form-label">Subject</label>
        <select name="subject_id" class="form-select mb-3" required>
            <option value="">Select Subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}"
                    @selected(old('subject_id') == $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>

        {{-- Topic --}}
        <label class="form-label">Topic ID</label>
        <input type="number"
               name="topic_id"
               class="form-control mb-3"
               value="{{ old('topic_id') }}"
               placeholder="Enter topic ID (optional)">

        {{-- Question Text --}}
        <label class="form-label">Question</label>
        <textarea name="question"
                  class="form-control mb-3"
                  rows="3"
                  required>{{ old('question_text') }}</textarea>

        {{-- Question Type --}}
        <label class="form-label">Question Type</label>
        <select name="question_type"
                class="form-select mb-3"
                required>
            <option value="single"
                @selected(old('question_type', 'single') == 'single')>
                Single Correct Answer
            </option>

            <option value="multiple"
                @selected(old('question_type') == 'multiple')>
                Multiple Correct Answers
            </option>
        </select>

        {{-- Options --}}
        <label class="form-label">Answer Options</label>

        <div id="options-container">

            @php
                $oldOptions = old('options', [
                    ['option_label' => 'A', 'option_text' => '', 'is_correct' => false],
                    ['option_label' => 'B', 'option_text' => '', 'is_correct' => false],
                    ['option_label' => 'C', 'option_text' => '', 'is_correct' => false],
                    ['option_label' => 'D', 'option_text' => '', 'is_correct' => false],
                ]);
            @endphp

            @foreach($oldOptions as $index => $option)
                <div class="row mb-3 option-row">

                    <div class="col-md-1">
                        <input type="text"
                               name="options[{{ $index }}][option_label]"
                               class="form-control"
                               value="{{ $option['option_label'] ?? chr(65 + $index) }}"
                               readonly>
                    </div>

                    <div class="col-md-8">
                        <input type="text"
                               name="options[{{ $index }}][option_text]"
                               class="form-control"
                               placeholder="Enter option"
                               value="{{ $option['option_text'] ?? '' }}"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-check mt-2">
                            <input type="checkbox"
                                   name="options[{{ $index }}][is_correct]"
                                   value="1"
                                   class="form-check-input"
                                   @checked(!empty($option['is_correct']))>
                            Correct
                        </label>
                    </div>

                    <div class="col-md-1">
                        @if($index >= 2)
                            <button type="button"
                                    class="btn btn-danger remove-option">
                                X
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach

        </div>

        <button type="button"
                class="btn btn-secondary btn-sm mb-3"
                id="add-option">
            + Add Option
        </button>

        {{-- Explanation --}}
        <label class="form-label">Explanation</label>
        <textarea name="explanation"
                  class="form-control mb-3"
                  rows="3">{{ old('explanation') }}</textarea>

        {{-- Marks --}}
        <div class="row mb-3">

            <div class="col-md-6">
                <label class="form-label">Positive Marks</label>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       name="marks"
                       value="{{ old('marks', 1) }}"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Negative Marks</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="negative_marks"
                       value="{{ old('negative_marks', 0.25) }}"
                       class="form-control"
                       required>
            </div>

        </div>

        {{-- Difficulty --}}
<label class="form-label">Difficulty Level</label>

<select name="difficulty" class="form-select mb-3" required>
    <option value="">Select Difficulty</option>

    <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>
        Easy
    </option>

    <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>
        Medium
    </option>

    <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>
        Hard
    </option>
</select>

        {{-- Status --}}
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select mb-3" required>
            <option value="1" @selected(old('is_active', '1') == '1')>
                Active
            </option>
            <option value="0" @selected(old('is_active') === '0')>
                Inactive
            </option>
        </select>

        <button type="submit" class="btn btn-primary mt-3">
            Save Question
        </button>

        <a href="{{ route('admin.questions.index') }}"
           class="btn btn-secondary mt-3">
            Cancel
        </a>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('options-container');
    const addButton = document.getElementById('add-option');

    function updateOptions() {
        const rows = container.querySelectorAll('.option-row');

        rows.forEach((row, index) => {
            row.querySelector('input[readonly]').value =
                String.fromCharCode(65 + index);

            row.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(
                    /options\[\d+\]/,
                    'options[' + index + ']'
                );
            });

            const removeButton = row.querySelector('.remove-option');

            if (removeButton) {
                removeButton.style.display =
                    index >= 2 ? 'block' : 'none';
            }
        });

        addButton.disabled = rows.length >= 10;
    }

    addButton.addEventListener('click', function () {

        const rows = container.querySelectorAll('.option-row');

        if (rows.length >= 10) return;

        const index = rows.length;
        const label = String.fromCharCode(65 + index);

        const row = document.createElement('div');

        row.className = 'row mb-3 option-row';

        row.innerHTML = `
            <div class="col-md-1">
                <input type="text"
                       class="form-control"
                       value="${label}"
                       readonly>
            </div>

            <div class="col-md-8">
                <input type="text"
                       name="options[${index}][option_text]"
                       class="form-control"
                       placeholder="Enter option"
                       required>
                <input type="hidden"
                       name="options[${index}][option_label]"
                       value="${label}">
            </div>

            <div class="col-md-2">
                <label class="form-check mt-2">
                    <input type="checkbox"
                           name="options[${index}][is_correct]"
                           value="1"
                           class="form-check-input">
                    Correct
                </label>
            </div>

            <div class="col-md-1">
                <button type="button"
                        class="btn btn-danger remove-option">
                    X
                </button>
            </div>
        `;

        container.appendChild(row);
        updateOptions();
    });

    container.addEventListener('click', function (event) {

        if (event.target.classList.contains('remove-option')) {
            event.target.closest('.option-row').remove();
            updateOptions();
        }

    });

    updateOptions();
});
</script>

@endsection
