<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('manageuser', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required',
            'email'      => 'required|email|unique:users',
            'role'       => 'required',
            'department' => 'required',
            'signature'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $signaturePath = null;

        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')
                                     ->store('signatures', 'public');
        }

        User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'role'                 => $request->role,
            'department'           => $request->department,
            'signature'            => $signaturePath,
            'must_change_password' => true,
            'password'             => Hash::make('sanoh123'),
        ]);

        return back()->with('success', 'User created successfully');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'       => 'required',
            'role'       => 'required',
            'department' => 'required',
            'signature'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name'       => $request->name,
            'role'       => $request->role,
            'department' => $request->department,
        ];

        if ($request->hasFile('signature')) {
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }
            $data['signature'] = $request->file('signature')
                                         ->store('signatures', 'public');
        }

        $user->update($data);

        return back()->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }
}