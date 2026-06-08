<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'SPT Dashboard')</title>

<!-- CSS -->
<link rel="stylesheet" href="{{ asset('css/layout.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

@stack('head')
</head>

<body>

<!-- HEADER -->
<header>
    <div style="display:flex; align-items:center; gap:15px;">
        <button class="menu-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars" style="font-size: 1.5rem;"></i>
        </button>

        <div class="title">
            @yield('page_title', 'Performance Dashboard')
        </div>
    </div>

    <div class="header-right">
        <span id="welcomeText" class="welcome-text"></span>

        <button class="logout-btn" onclick="toggleLogoutPopup()">
            <i class="fa-regular fa-circle-user" style="font-size: 1.5rem;"></i>
        </button>

        <div id="logoutPopup" class="logout-popup">
            <div class="logout-text">Do you want to logout?</div>

            <div class="logout-actions">
                <button onclick="confirmLogout()" class="yes">Yes</button>
                <button onclick="toggleLogoutPopup()" class="no">No</button>
            </div>
        </div>
    </div>
</header>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('images/sanohlogo.png') }}" alt="Logo">
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

        <a href="{{ url('/report') }}">
        class="{{ request()->is('report') ? 'active' : '' }}">            
            <i class="fas fa-print"></i> 
            Print Report
        </a>

        <div class="divider"></div>

        <a href="{{ url('/delivery') }}"
        class="{{ request()->is('delivery') ? 'active' : '' }}">
            <i class="fas fa-truck"></i>
            Delivery Input
        </a>

        <a href="{{ url('/qcinspection') }}">
        class="{{ request()->is('qcinspection') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> 
            QC Inspection
        </a>

        <a href="{{ url('/approval') }}"
        class="{{ request()->is('approval') ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i>
            Approval Workflow
        </a>

        <div class="divider"></div>

        <a href="{{ url('/delivhistory') }}">
        class="{{ request()->is('delivhistory') ? 'active' : '' }}">
            <i class="fas fa-history"></i> 
            Delivery History
        </a>

        <a href="{{ url('/qchistory') }}">
        class="{{ request()->is('qchistory') ? 'active' : '' }}">
            <i class="fas fa-check-double"></i> 
            QC History
        </a>
    </div>

    <!-- CONTENT -->
    <div class="content">
        @yield('content')
    </div>

</div>

<!-- GLOBAL JS -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const currentUser = localStorage.getItem("userLogin");

    const welcome = document.getElementById("welcomeText");
    if (welcome) {
        welcome.textContent = `Hi, ${currentUser ?? 'User'}`;
    }

    const dateEl = document.getElementById("date");
    if (dateEl) {
        dateEl.innerText = new Date().toLocaleDateString('en-US', {
            weekday:'long',
            year:'numeric',
            month:'long',
            day:'numeric'
        });
    }
});

function toggleLogoutPopup(){
    const popup = document.getElementById("logoutPopup");
    popup.style.display = popup.style.display === "block" ? "none" : "block";
}

function confirmLogout(){
    document.getElementById("logoutForm")
        .submit();
}

function toggleSidebar(){
    document.querySelector(".sidebar").classList.toggle("hide");
    document.querySelector(".content").classList.toggle("full");
    document.querySelector("header").classList.toggle("full");
}
</script>

@stack('scripts')

<form id="logoutForm"
      action="{{ route('logout') }}"
      method="POST"
      style="display:none;">

    @csrf

</form>

</body>
</html>