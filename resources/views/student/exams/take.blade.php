@extends('layouts.app')

@section('heading', $exam->title)

@push('styles')
<style>
    .question-card {
        background: white;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }

    .exam-timer {
        position: sticky;
        top: 10px;
        z-index: 10;
        background: #182337;
        color: white;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }

    .option {
        display: block;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 7px;
        margin: 8px 0;
        cursor: pointer;
    }
</style>
@endpush

@section('content')

<div class="exam-timer mb-4">
    <h5 class="mb-0">
        Time Remaining:
        <span id="timer">--:--</span>
    </h5>
</div>

<form id="examForm"
      method="POST"
      action="{{ route('student.attempts.submit', $attempt) }}">

    @csrf

    @foreach($answers as $index => $answer)

        @php
            $question = $questions[$answer->question_id];
        @endphp

        <div class="question-card">

            <h6>
                Question {{ $loop->iteration }}
                <span class="text-muted">
                    ({{ $question->marks }} marks)
                </span>
            </h6>

            <p class="fs-5">{{ $question->question }}</p>

            @foreach(['A', 'B', 'C', 'D'] as $option)
                @php
                    $field = 'option_' . strtolower($option);
                @endphp

                <label class="option">
                    <input type="radio"
                           name="question_{{ $question->id }}"
                           value="{{ $option }}"
                           data-question="{{ $question->id }}"
                           @checked($answer->selected_option === $option)>

                    {{ $option }}.
                    {{ $question->$field }}
                </label>
            @endforeach

            <small class="text-muted" id="saved-{{ $question->id }}">
                Answer will be saved automatically.
            </small>

        </div>

    @endforeach

    <button type="submit" class="btn btn-success btn-lg">
        Submit Examination
    </button>

</form>

@endsection

@push('scripts')
<script>
const endTime = new Date(
    @json($attempt->ends_at->toIso8601String())
).getTime();

const answerUrl = @json(
    route('student.attempts.answer', $attempt)
);

const submitForm = document.getElementById('examForm');

let expired = false;

function submitExam() {
    if (expired) return;

    expired = true;

    submitForm.requestSubmit();
}

function updateTimer() {
    const remaining = Math.max(
        0,
        Math.floor((endTime - Date.now()) / 1000)
    );

    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;

    document.getElementById('timer').textContent =
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');

    if (remaining <= 0) {
        submitExam();
    }
}

updateTimer();

const timerInterval = setInterval(updateTimer, 1000);

document.querySelectorAll('input[type=radio]').forEach(input => {

    input.addEventListener('change', async function() {

        const questionId = this.dataset.question;

        const status = document.getElementById(
            'saved-' + questionId
        );

        status.textContent = 'Saving...';

        try {
            const response = await fetch(answerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    selected_option: this.value
                })
            });

            if (response.status === 409) {
                submitExam();
                return;
            }

            if (!response.ok) {
                throw new Error('Save failed');
            }

            status.textContent = 'Saved ✓';

        } catch (error) {
            status.textContent = 'Save failed. Please retry.';
        }
    });
});

submitForm.addEventListener('submit', function() {
    clearInterval(timerInterval);

    submitForm.querySelectorAll('button').forEach(button => {
        button.disabled = true;
    });
});
</script>
@endpush