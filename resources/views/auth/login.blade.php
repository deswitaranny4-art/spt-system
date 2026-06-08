<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>SPT - Login</title>

<link rel="stylesheet" href="{{ asset('css/signin.css') }}">

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="login-wrapper">

    <!-- LEFT -->
    <div class="left-panel">

        <div class="login-card">

            <div class="brand">
                Supplier Performance Tracker
            </div>

            <h1>Welcome Back</h1>

            <p class="subtitle">
                Log in to continue your workflow
            </p>

            <!-- FORM LOGIN -->
            <form method="POST"
                  action="{{ route('login') }}">

                @csrf

                <!-- EMAIL -->
                <div class="form-group">

                    <label>Email</label>

                    <div class="input-box">

                        <i class="fa-regular fa-user"></i>

                        <input type="email"
                               name="email"
                               id="email"
                               placeholder="Enter email"
                               required>

                    </div>

                </div>

                <!-- PASSWORD -->
                <div class="form-group">

                    <label>Password</label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input type="password"
                               name="password"
                               id="password"
                               placeholder="Enter password"
                               required>

                        <button type="button"
                                class="show-btn"
                                onclick="togglePassword()">

                            <i class="fa-regular fa-eye"
                               id="eyeIcon"></i>

                        </button>

                    </div>

                </div>

                <!-- ERROR -->
                @if ($errors->any())

                    <div style="
                        color:red;
                        margin-bottom:15px;
                        font-size:14px;
                    ">
                        Invalid email or password
                    </div>

                @endif

                <!-- BUTTON -->
                <button type="submit"
                        class="login-btn">

                    Log In

                </button>

                <!-- OPTIONS -->
                    <div class="options-row">

                <label class="remember">

                </label>

                <a href="{{ route('password.request') }}"
                class="forgot-link">

                Forgot Password?

                </a>

                </div>

            </form>

            <div class="footer-text">
                PT Sanoh Indonesia
            </div>

        </div>

    </div>

    <!-- RIGHT -->
    <div class="right-panel">

        <img src="{{ asset('images/sanohcomp.jpg') }}"
             class="bg-image">

        <div class="overlay"></div>

        <div class="hero-content">

            <div class="hero-title">
                Supplier Performance Tracker
            </div>

            <div class="hero-subtitle">
                Quality • Delivery • Performance
            </div>

        </div>

    </div>

</div>

<script>

/* =========================
   SHOW PASSWORD
========================= */

function togglePassword(){

    const input =
        document.getElementById("password");

    const icon =
        document.getElementById("eyeIcon");

    if(input.type === "password"){

        input.type = "text";

        icon.classList.remove("fa-eye");

        icon.classList.add("fa-eye-slash");

    }else{

        input.type = "password";

        icon.classList.remove("fa-eye-slash");

        icon.classList.add("fa-eye");
    }
}

</script>

</body>
</html>