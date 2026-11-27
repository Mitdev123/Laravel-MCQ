<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'MCQ Examination System')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: #182337;
            color: white;
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #dbe3ef;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background: #293c59;
            color: white;
        }
        .main-content {
            padding: 25px;
        }
        .stat-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <aside class="col-md-2 sidebar p-3">
            <h5 class="mb-4">MCQ SYSTEM</h5>

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.subjects.index') }}">
                        <i class="bi bi-book"></i> Subjects
                    </a>
                    <a href="{{ route('admin.questions.index') }}">
                        <i class="bi bi-question-circle"></i> Questions
                    </a>
                    <a href="{{ route('admin.questions.import') }}">
                        <i class="bi bi-file-earmark-excel"></i> Import Questions
                    </a>
                    <a href="{{ route('admin.exams.index') }}">
                        <i class="bi bi-pencil-square"></i> Exams
                    </a>
                    <a href="{{ route('admin.students.index') }}">
                        <i class="bi bi-people"></i> Students
                    </a>
                @else
                    <a href="{{ route('student.dashboard') }}">Dashboard</a>
                    <a href="{{ route('student.exams.index') }}">Examinations</a>
                    <a href="{{ route('student.results.index') }}">My Results</a>
                @endif

                <hr>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light w-100">
                        Logout
                    </button>
                </form>
            @endauth
        </aside>

        <main class="col-md-10 main-content">

            <div class="d-flex justify-content-between mb-4">
                <h4>@yield('heading', 'Dashboard')</h4>
                @auth
                    <span>{{ auth()->user()->name }}</span>
                @endauth
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>