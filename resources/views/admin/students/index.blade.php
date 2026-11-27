<!DOCTYPE html>
<html>
<head>
    <title>Students</title>
</head>
<body>

    <h1>Student Management</h1>

    <h3>Students List</h3>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
        </tr>

        @forelse($students ?? [] as $student)
            <tr>
                <td>{{ $student->id }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No students found.</td>
            </tr>
        @endforelse
    </table>

</body>
</html>