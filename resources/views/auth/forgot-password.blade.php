<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password</title>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

<link rel="stylesheet" 
href="{{ asset('css/forgot-password.css') }}">

</head>

<body>

<div class="container">

    <div class="title">
        Forgot Password
    </div>

    <div class="subtitle">
        Enter your registered email address and we will send you a password reset link.
    </div>

    @if (session('status'))

        <div class="success">
            {{ session('status') }}
        </div>

    @endif

    <form method="POST"
          action="{{ route('password.email') }}">

        @csrf

        <div class="input-box">

            <input type="email"
                   name="email"
                   placeholder="Enter registered email"
                   required>

        </div>

        @error('email')

            <div class="error">
                {{ $message }}
            </div>

        @enderror

        <button type="submit"
                class="reset-btn">

            Reset Password

        </button>

    </form>

</div>

</body>
</html>