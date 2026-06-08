<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reset Password</title>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('css/reset-password.css') }}">

</head>

<body>

<div class="container">

    <div class="title">
        Reset Password
    </div>

    <div class="subtitle">
        Enter your new password below
    </div>

    @if (session('status'))
    <div style="color:green; margin-bottom:15px;">
        {{ session('status') }}
    </div>
    @endif

    @if ($errors->any())
        <div style="color:red; margin-bottom:15px;">
            {{ $errors->first() }}
        </div>
    @endif

<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- EMAIL -->
    <div class="form-group">
        <label>Email</label>
        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
        </div>
    </div>

    <!-- PASSWORD -->
    <div class="form-group">
        <label>New Password</label>
        <div class="input-box">
            <input type="password" name="password" required>
        </div>
    </div>

    <!-- CONFIRM -->
    <div class="form-group">
        <label>Confirm Password</label>
        <div class="input-box">
            <input type="password" name="password_confirmation" required>
        </div>
    </div>

    <button type="submit" class="save-btn">
        Reset Password
    </button>

    </form>

</div>

</body>
</html>