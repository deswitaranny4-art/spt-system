<?php

namespace App\Http\Controllers;

use App\Models\PasswordHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[0-9]/' // wajib ada angka
            ],
        ]);

        $user = Auth::user();

        // CEK PASSWORD LAMA
        $oldPasswords = PasswordHistory::where('user_id', $user->id)->get();

        foreach ($oldPasswords as $old) {
            if (Hash::check($request->password, $old->password)) {
                return back()->withErrors([
                    'password' => 'Password sudah pernah digunakan sebelumnya.'
                ]);
            }
        }

        // SIMPAN PASSWORD BARU
        $user->password = Hash::make($request->password);
        $user->first_login = false;
        $user->must_change_password = false;
        $user->save();

        // SIMPAN KE HISTORY
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $user->password
        ]);

        return redirect('/login')
            ->with('success', 'Password berhasil diubah, silakan login kembali.');
    }
}