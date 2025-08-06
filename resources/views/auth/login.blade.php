<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - {{ env('APP_NAME', 'Default Title') }}</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/x-icon">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f3f4f6; /* Latar belakang abu-abu muda */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .container {
            max-width: 400px; /* Lebar container lebih kecil untuk form login */
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .btn-login {
            background-color: #0073aa; /* Warna biru WordPress */
            border-color: #0073aa;
            color: #fff;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            border-radius: 4px;
            width: 100%; /* Tombol lebar penuh */
        }
        .btn-login:hover {
            background-color: #005a87;
            border-color: #005a87;
            color: #fff;
        }
        .login-text {
            color: #555;
            font-size: 14px;
            text-align: center;
        }
        .login-text a {
            color: #0073aa;
            text-decoration: none;
        }
        .login-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center mb-4">Login</h3>

    <form action="#" method="POST">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Ingat Saya</label>
        </div>

        <button type="submit" class="btn btn-login mb-3">Log In</button>
    </form>

    <div class="login-text">
        <a href="{{ route('password') }}">Lupa Password?</a>
    </div>
</div>

<!-- Bootstrap JS CDN (opsional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
