<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Change Password</title>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" 
href="{{ asset('css/changepassword.css') }}">

</head>

<body>

<div class="container">

    <div class="title">
        Change Password
    </div>

    <div class="subtitle">
        You must change your password before continuing
    </div>

    <form method="POST"
      action="/change-password">

        @csrf

        <!-- NEW PASSWORD -->
        <div class="form-group">

            <label>New Password</label>

            <div class="input-box">

                <input type="password"
                       name="password"
                       placeholder="Enter new password"
                       required>

                <i class="fa-solid fa-lock"></i>

            </div>

            @error('password')
                <div class="error">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="form-group">

            <label>Confirm Password</label>

            <div class="input-box">

                <input type="password"
                       name="password_confirmation"
                       placeholder="Confirm password"
                       required>

                <i class="fa-solid fa-lock"></i>

            </div>

        </div>

        <button type="submit"
                class="save-btn">

            Save Password

        </button>

    </form>

</div>

</body>
</html>