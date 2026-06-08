<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'SPT Dashboard')</title>

<!-- GLOBAL CSS -->

<link rel="stylesheet" href="{{ asset('css/layout.css') }}">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap"
      rel="stylesheet">

<!-- OPTIONAL PAGE CSS -->

@stack('head')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- SUPABASE -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
    const { createClient } = supabase;
    const db = createClient(
        "https://ulmzjwhlhhivpkmtbipi.supabase.co",
        "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVsbXpqd2hsaGhpdnBrbXRiaXBpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Nzg1MDExODQsImV4cCI6MjA5NDA3NzE4NH0.z-UoSAI5gDJBIw1_9Y4YYR9FSD41xj05TYAIdImpYrc"
    );
</script>

</head>

<body>

<!-- HEADER -->

<header>

<div style="display:flex; align-items:center; gap:15px;">

    <button class="menu-btn"
            onclick="toggleSidebar()">

        <i class="fa-solid fa-bars"
           style="font-size: 1.5rem;"></i>

    </button>

    <!-- PAGE TITLE -->
    <div class="title">

        @yield('page_title', 'Dashboard')

    </div>

</div>

<div class="header-right">

    <!-- PROFILE -->
    <div class="profile-wrapper">

        <div class="welcome-text"
             id="welcomeText"
             onclick="toggleProfilePopup()">

            Hi, {{ Auth::user()?->name ?? 'User' }}

        </div>

        <!-- PROFILE POPUP -->
        <div class="profile-popup"
             id="profilePopup">

            <div class="profile-name"
                 id="profileName">

                {{ Auth::user()->name ?? 'User' }}

            </div>

            <div class="profile-detail">

                <span>Role</span>

                <b id="profileRole">

                    {{ Auth::user()->role ?? '-' }}

                </b>

            </div>

            <div class="profile-detail">

                <span>Department</span>

                <b id="profileDept">

                    {{ Auth::user()->department ?? '-' }}

                </b>

            </div>

        </div>

    </div>

    <!-- LOGOUT BUTTON -->
    <div class="logout-wrapper">

        <button class="logout-btn"
                onclick="toggleLogoutPopup()">

            <i class="fa-regular fa-circle-user"
            style="font-size:1.5rem;"></i>

        </button>

        <!-- LOGOUT POPUP -->
        <div id="logoutPopup"
            class="logout-popup">

            <div class="logout-text">

                Do you want to logout?

            </div>

            <div class="logout-actions">

                <!-- YES -->
                <button type="button"
                        class="yes"
                        onclick="document.getElementById('logout-form').submit()">

                    Yes

                </button>

                <!-- NO -->
                <button type="button"
                        class="no"
                        onclick="toggleLogoutPopup()">

                    No

                </button>

            </div>

        </div>

    </div>

</div>

</header>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

    @php
    $userDept = Auth::user()?->department;
    $userRole = Auth::user()?->role;
@endphp

    <div class="sidebar-logo">

        <img src="{{ asset('images/sanohlogo.png') }}"
             alt="Logo">

    </div>

    <a href="{{ url('/dashboard') }}"
    class="{{ request()->is('dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i>
        Dashboard
    </a>

    <a href="{{ url('/ranking') }}"
    class="{{ request()->is('ranking') ? 'active' : '' }}">
        <i class="fas fa-trophy"></i>
        Supplier Ranking
    </a>

    @if($userDept == 'Purchasing' || $userRole == 'Admin')
    <a href="{{ url('/report') }}"
    class="{{ request()->is('report') ? 'active' : '' }}">
        <i class="fas fa-print"></i>
        Print Report
    </a>
    @endif

    @if($userDept == 'Warehouse' || $userRole == 'Admin')
    <a href="{{ url('/delivery/input') }}"
    class="{{ request()->is('delivery/input') ? 'active' : '' }}">
        <i class="fas fa-truck"></i>
        Input Delivery
    </a>

    @endif

    @if($userDept == 'Quality Control' || $userRole == 'Admin')
    <a href="{{ url('/qc/inspection') }}"
    class="{{ request()->is('qc/inspection') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i>
        QC Inspection
    </a>
    @endif

    @if($userDept == 'Warehouse' || $userRole == 'Admin')

    <a href="{{ url('/delivery/history') }}"
    class="{{ request()->is('delivery/history') ? 'active' : '' }}">
        <i class="fas fa-history"></i>
        Delivery History
    </a>
    @endif

    @if($userDept == 'Quality Control' || $userRole == 'Admin')
    <a href="{{ url('/qc/history') }}"
    class="{{ request()->is('qc/history') ? 'active' : '' }}">
        <i class="fas fa-check-double"></i>
        QC History
    </a>

    @endif

    @if($userRole == 'Admin')
    <a href="{{ url('/manage-user') }}"
    class="{{ request()->is('manage-user') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        Manage User
    </a>
    @endif

    @if($userRole == 'Admin'

    ||

    ($userRole == 'Supervisor'
        && $userDept == 'Quality Control')

    ||

    ($userRole == 'Manager'
        && $userDept == 'Quality Control')

    ||

    ($userRole == 'Supervisor'
        && $userDept == 'PPIC')

    ||

    ($userRole == 'Manager'
        && $userDept == 'PPIC')

    ||

    ($userRole == 'Leader'
        && $userDept == 'Purchasing')

    ||

    ($userRole == 'Manager'
        && $userDept == 'Purchasing')

    ||

    ($userRole == 'General Manager'
        && $userDept == 'Production')

)
    <a href="{{ url('/approval') }}"
    class="{{ request()->is('approval') ? 'active' : '' }}">
        <i class="fas fa-check-circle"></i>
        Approval Workflow
    </a>

    @endif

</div>

<!-- CONTENT -->
<div class="content">

    @yield('content')

</div>

</div>

<!-- LOGOUT FORM -->
<form id="logout-form"
      action="{{ route('logout') }}"
      method="POST"
      style="display:none;">

    @csrf

</form>

<script>

/* =========================
   PROFILE POPUP
========================= */

function toggleProfilePopup(){

    document
        .getElementById("profilePopup")
        .classList.toggle("show");

    document
        .getElementById("logoutPopup")
        .classList.remove("show");
}

/* =========================
   LOGOUT POPUP
========================= */

function toggleLogoutPopup(){

    document
        .getElementById("logoutPopup")
        .classList.toggle("show");

    document
        .getElementById("profilePopup")
        .classList.remove("show");
}

/* =========================
   CLOSE POPUP OUTSIDE
========================= */

document.addEventListener("click", function(e){

    const profile =
        document.querySelector(".profile-wrapper");

    const logout =
        document.querySelector(".logout-wrapper");

    /* PROFILE */
    if(
        profile &&
        !profile.contains(e.target)
    ){

        document
            .getElementById("profilePopup")
            .classList.remove("show");
    }

    /* LOGOUT */
    if(
        logout &&
        !logout.contains(e.target)
    ){

        document
            .getElementById("logoutPopup")
            .classList.remove("show");
    }

});

/* =========================
   SIDEBAR
========================= */

function toggleSidebar(){

    document
        .querySelector(".sidebar")
        .classList.toggle("hide");

    document
        .querySelector(".content")
        .classList.toggle("full");

    document
        .querySelector("header")
        .classList.toggle("full");
}

</script>

@stack('scripts')

</body>
</html>