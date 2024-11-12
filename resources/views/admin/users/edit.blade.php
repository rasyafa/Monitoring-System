<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f1f1;
            /* Mengganti latar belakang menjadi abu muda */
            font-family: sans-serif;
        }

        .container {
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            /* Warna putih untuk kontainer form */
        }

        h2 {
            color: #333;
        }

        .form-label {
            color: #333;
        }

        .btn-primary {
            background-color: #17d033;
            border-color: #00b158;
        }

        .alert-danger {
            background-color: #e19c91ab;
            border-color: #de8d8dbe;
            color: #fff;
        }

        .btn-success {
            background-color: #17d033;
            border-color: #48d75d;
        }

        .btn-success:hover {
            background-color: #169e28;
            /* Warna latar belakang saat hover */
            border-color: #3bb14b;
            /* Warna border saat hover */
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Edit User</h2>
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}">
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                    value="{{ old('username', $user->username) }}">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ old('email', $user->email) }}">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role', $user->role) == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update User</button>
        </form>
    </div>
</body>

</html>