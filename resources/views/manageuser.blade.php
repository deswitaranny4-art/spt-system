@extends('layouts.app')

@section('title', 'Manage User')
@section('page_title', 'Manage User')

@push('head')

<link rel="stylesheet" href="{{ asset('css/manageuser.css') }}">
@endpush

@section('content')

<div class="main-content">

<!-- SUCCESS MESSAGE -->
@if(session('success'))

<div id="successPopup" class="popup-overlay">

    <div class="popup-box">

        <div class="popup-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <div class="popup-title">
            Success
        </div>

        <div class="popup-message">
            {{ session('success') }}
        </div>

        <button onclick="closePopup()"
                class="popup-btn">

            OK

        </button>

    </div>

</div>

@endif

<!-- ADD USER -->
<form method="POST"
      action="/manage-user"
      enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-title">
            Add New User
        </div>

        <!-- ROW 1 -->
        <div class="form-grid">
            <div class="input-group">
                <label>Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       required>
            </div>

            <div class="input-group">

                <label>Email</label>

                <input type="email"
                       id="email"
                       name="email"
                       required>

            </div>

        </div>

        <!-- ROW 2 -->
        <div class="form-grid">

            <div class="input-group">

                <label>Role</label>

                <select id="role"
                        name="role"
                        required>

                    <option value="">
                        Select Role
                    </option>
                    <option value="Admin">Admin</option>
                    <option value="Staff">Staff</option>
                    <option value="Leader">Leader</option>
                    <option value="Supervisor">Supervisor</option>
                    <option value="Manager">Manager</option>

                    <option value="General Manager">
                        General Manager
                    </option>

                </select>

            </div>

            <div class="input-group">

                <label>Department</label>

                <select id="department"
                        name="department"
                        required>

                    <option value="">
                        Select Department
                    </option>

                    <option value="Production">
                        Production
                    </option>

                    <option value="Warehouse">
                        Warehouse
                    </option>

                    <option value="Purchasing">
                        Purchasing
                    </option>

                    <option value="HRGA">
                        HRGA
                    </option>

                    <option value="Accounting">
                        Accounting
                    </option>

                    <option value="Sales Marketing">
                        Sales Marketing
                    </option>

                    <option value="Engineering">
                        Engineering
                    </option>

                    <option value="Quality Control">
                        Quality Control
                    </option>

                    <option value="PPIC">
                        PPIC
                    </option>

                    <option value="IT">
                    IT
                </option>

                </select>

            </div>

        </div>

        <!-- SIGNATURE -->
        <div class="input-group signature-upload">

            <label>Upload Signature</label>

            <input type="file"
                   id="signature"
                   name="signature"
                   accept="image/*">

        </div>

        <button type="submit"
                class="add-btn">

            <i class="fa-solid fa-user-plus"></i>

            Add User

        </button>

    </div>

</form>

<!-- USER TABLE -->
<div class="card">

    <div class="card-title">
        User List
    </div>

    <table class="user-table">

        <thead>

            <tr>

                <th>Email</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>Action</th>

            </tr>

        </thead>

        <tbody>

            @forelse($users as $user)

            <tr>

                <td>{{ $user->email }}</td>

                <td>{{ $user->name }}</td>

                <td>

                    <span class="role-badge">
                        {{ $user->role }}
                    </span>

                </td>

                <td>{{ $user->department }}</td>

                <td>

                   <div class="action-buttons">
 
    <!-- EDIT -->
 <button class="edit-btn"
        onclick="openEditModal(
            {{ $user->id }},
            '{{ $user->name }}',
            '{{ $user->email }}',
            '{{ $user->role }}',
            '{{ $user->department }}'
        )">
    <i class="fa-solid fa-pen"></i>
</button>
    <!-- DELETE -->
<button class="delete-icon"
        onclick="openDeletePopup({{ $user->id }}, '{{ $user->name }}')">
    <i class="fa-solid fa-trash"></i>
</button>

<!-- DELETE POPUP -->
<div id="deletePopup" class="popup-overlay" style="display:none;">
    <div class="popup-box">

        <div class="popup-icon delete">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <div class="popup-title">
            Delete User
        </div>

        <div class="popup-message">
            Are you sure you want to delete <br>
            <strong id="deleteUserName"></strong>?
        </div>

        <div class="popup-actions">

            <button onclick="closeDeletePopup()"
                    class="popup-btn cancel">
                Cancel
            </button>

            <button onclick="submitDelete()"
                    class="popup-btn danger">
                <i class="fa-solid fa-trash"></i>
                Delete
            </button>

        </div>

    </div>
</div>

<!-- HIDDEN DELETE FORM -->
<form id="deleteForm"
      method="POST"
      style="display:none;">
    @csrf
    @method('DELETE')
</form>
</div>

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="5">
                    No User Data
                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

</div>

</div>
<!-- EDIT MODAL -->
<div id="editModal" class="popup-overlay" style="display:none;">
    <div class="popup-box" style="width:480px;">

        <div class="popup-title">
            Edit User
        </div>

        <form id="editForm"
      method="POST"
      enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid" style="margin-top:20px;">

                <div class="input-group">
                    <label>Name</label>
                    <input type="text"
                           id="editName"
                           name="name"
                           required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email"
                           id="editEmail"
                           disabled>
                </div>

            </div>

            <div class="form-grid">

                <div class="input-group">
                    <label>Role</label>
                    <select id="editRole" name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Staff">Staff</option>
                        <option value="Leader">Leader</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Manager">Manager</option>
                        <option value="General Manager">General Manager</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Department</label>
                    <select id="editDept" name="department" required>
                        <option value="Production">Production</option>
                        <option value="Warehouse">Warehouse</option>
                        <option value="Purchasing">Purchasing</option>
                        <option value="HRGA">HRGA</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Sales Marketing">Sales Marketing</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Quality Control">Quality Control</option>
                        <option value="PPIC">PPIC</option>
                        <option value="IT">IT</option>
                    </select>
                </div>

</div>

<div class="input-group signature-upload"
     style="margin-bottom:20px;">
    <label>Upload Signature</label>
    <input type="file"
           id="editSignature"
           name="signature"
           accept="image/*">
            </div>

            <div style="display:flex; gap:12px; margin-top:10px;">

                <button type="submit" class="add-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save
                </button>

                <button type="button"
                        onclick="closeEditModal()"
                        class="add-btn"
                        style="background:#6b7280;">
                    Cancel
                </button>

            </div>

        </form>

    </div>
</div>
<script>

/* SUCCESS POPUP */
function closePopup(){
    document.getElementById("successPopup").style.display = "none";
}

/* DELETE POPUP */
function openDeletePopup(userId, userName){
    document.getElementById("deleteUserName").textContent = userName;
    document.getElementById("deleteForm").action = "/manage-user/" + userId;
    document.getElementById("deletePopup").style.display = "flex";
}

function closeDeletePopup(){
    document.getElementById("deletePopup").style.display = "none";
}

function submitDelete(){
    document.getElementById("deleteForm").submit();
}

/* EDIT MODAL */
function openEditModal(id, name, email, role, dept){
    document.getElementById("editName").value  = name;
    document.getElementById("editEmail").value = email;
    document.getElementById("editRole").value  = role;
    document.getElementById("editDept").value  = dept;
    document.getElementById("editForm").action = "/manage-user/" + id;
    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal(){
    document.getElementById("editModal").style.display = "none";
}

</script>
</script>

@endsection