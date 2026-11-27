<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\StudentController;

use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\PracticeController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\AdminController;

//Route::get('/admin/import', [AdminController::class, 'import'])
  //  ->name('admin.import');


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('student.dashboard');
    })->name('dashboard');

    // ADMIN PANEL
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboard::class, 'index'])
                ->name('dashboard');

            Route::resource('subjects', SubjectController::class)
                ->except(['show']);

            Route::resource('topics', TopicController::class)
                ->except(['show']);

            Route::resource('questions', QuestionController::class)
                ->except(['show', 'edit', 'update']);

            Route::resource('exams', AdminExamController::class)
                ->except(['show', 'edit', 'update']);

            Route::get('/question-import', [
                QuestionImportController::class, 'index'
            ])->name('questions.import');

            Route::post('/question-import', [
                QuestionImportController::class, 'store'
            ])->name('questions.import.store');

            Route::resource('students', StudentController::class);
        });

    // STUDENT PANEL
    Route::prefix('student')
        ->name('student.')
        ->middleware('student')
        ->group(function () {

            Route::get('/dashboard', [StudentDashboard::class, 'index'])
                ->name('dashboard');

            Route::get('/exams', [StudentExamController::class, 'index'])
                ->name('exams.index');

            Route::post('/exams/{exam}/start', [
                StudentExamController::class, 'start'
            ])->name('exam.start');

            Route::get('/attempt/{attempt}', [
                StudentExamController::class, 'take'
            ])->name('exam.take');

            Route::post('/attempt/{attempt}/answer', [
                StudentExamController::class, 'saveAnswer'
            ])->name('exam.answer');

            Route::post('/attempt/{attempt}/submit', [
                StudentExamController::class, 'submit'
            ])->name('exam.submit');

            Route::get('/practice', [
                PracticeController::class, 'index'
            ])->name('practice.index');

            Route::post('/practice/{exam}/start', [
                PracticeController::class, 'start'
            ])->name('practice.start');

            Route::get('/results', [ResultController::class, 'index'])
                ->name('results.index');

            Route::get('/results/{attempt}', [
                ResultController::class, 'show'
            ])->name('result.show');
        });

        Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Question Excel Import
        Route::get(
            'questions/import',
            [QuestionImportController::class, 'index']
        )->name('questions.import');

        Route::get(
            'questions/import/template',
            [QuestionImportController::class, 'template']
        )->name('questions.import.template');

        Route::post(
            'questions/import/upload',
            [QuestionImportController::class, 'upload']
        )->name('questions.import.upload');

        Route::get(
            'questions/import/{batch}/preview',
            [QuestionImportController::class, 'preview']
        )->name('questions.import.preview');

        Route::post(
            'questions/import/{batch}/confirm',
            [QuestionImportController::class, 'confirm']
        )->name('questions.import.confirm');

        Route::delete(
            'questions/import/{batch}/cancel',
            [QuestionImportController::class, 'cancel']
        )->name('questions.import.cancel');

        // Question CRUD
        //Route::resource('questions', QuestionController::class);

    });
});

require __DIR__.'/auth.php';