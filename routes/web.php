<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\QCController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ChangePasswordController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});


Route::middleware('auth')->group(function () {

/*
|--------------------------------------------------------------------------
| API SUPPLIER
|--------------------------------------------------------------------------
*/

Route::get('/api/suppliers', [
    SupplierController::class,
    'index'
]);

Route::get(
    '/approval',
    [ApprovalController::class, 'index']
);

Route::post(

    '/approval/update',

    [ApprovalController::class, 'update']
);

    Route::post('/logout', function () {

        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect('/login');

    })->name('logout');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

Route::get('/dashboard', function () {
    $approvedDocs = DB::table('approvals')
        ->where('status', 'APPROVED')
        ->pluck('doc_number')
        ->toArray();

    $qcData = DB::table('qc')
        ->whereIn('docNumber', $approvedDocs)
        ->get();

    $deliveryData = DB::table('delivery')
        ->whereIn('docNumber', $approvedDocs)
        ->get();

    return view('dashboard', compact('qcData', 'deliveryData'));
})->name('dashboard');

 Route::get('/ranking', function () {

    $approvedDocs = DB::table('approvals')
        ->where('status', 'APPROVED')
        ->pluck('doc_number')
        ->toArray();

    $qcData = DB::table('qc')
        ->whereIn('docNumber', $approvedDocs)
        ->get();

    $deliveryData = DB::table('delivery')
        ->whereIn('docNumber', $approvedDocs)
        ->get();

    return view('ranking', compact('qcData', 'deliveryData'));
});

    Route::get(
        '/approval',
        [ApprovalController::class, 'index']
    );
/*
    |--------------------------------------------------------------------------
    | change pass
    |--------------------------------------------------------------------------
    */
    Route::get('/change-password', function () {
    return view('auth.force-change-password');
})->name('password.change');

Route::post('/change-password',
    [ChangePasswordController::class, 'update']
)->name('change.password.update');

    /*
    |--------------------------------------------------------------------------
    | REPORT
    |--------------------------------------------------------------------------
    */

    Route::get('/report', [
        ReportController::class,
        'index'
    ]);

    /*
    |--------------------------------------------------------------------------
    | MANAGE USER
    |--------------------------------------------------------------------------
    */

    Route::get('/manage-user', function () {
    $users = User::all();
    return view('manageuser', compact('users'));
});

Route::post('/manage-user', function (Request $request) {
    $request->validate([
        'name'       => 'required',
        'email'      => 'required|email|unique:users',
        'role'       => 'required',
        'department' => 'required',
    ]);

    $signaturePath = null;
    if($request->hasFile('signature')){
        $signaturePath = $request
            ->file('signature')
            ->store('signatures', 'public');
    }

    User::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'password'   => bcrypt('12345678'),
        'role'       => $request->role,
        'department' => $request->department,
        'signature'  => $signaturePath,
            // FORCE CHANGE PASSWORD
    'first_login' => true,
    'must_change_password' => true,
]);

    return redirect('/manage-user')
        ->with('success', 'User added successfully');
});

Route::put('/manage-user/{id}', function (Request $request, $id) {

    $user = User::findOrFail($id);

    $data = [
        'name'       => $request->name,
        'role'       => $request->role,
        'department' => $request->department,
    ];

    if($request->hasFile('signature')){
        $signaturePath = $request
            ->file('signature')
            ->store('signatures', 'public');
        $data['signature'] = $signaturePath;
    }

    $user->update($data);

    return redirect('/manage-user')
        ->with('success', 'User updated successfully');
});

Route::delete('/manage-user/{id}', function ($id) {
    User::findOrFail($id)->delete();
    return redirect('/manage-user')
        ->with('success', 'User deleted successfully');
});
    /*
    |--------------------------------------------------------------------------
    | DELIVERY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/delivery/input',
        [DeliveryController::class, 'input']
    );

    Route::get(
        '/delivery/history',
        [DeliveryController::class, 'history']
    );

    Route::post(
        '/delivery/store',
        [DeliveryController::class, 'store']
    );
Route::get('/delivery/edit/{id}', [DeliveryController::class, 'edit']);
Route::put('/delivery/update/{id}', [DeliveryController::class, 'update']);


    /*
    |--------------------------------------------------------------------------
    | QC
    |--------------------------------------------------------------------------
    */

    Route::prefix('qc')->group(function () {

        Route::get(
            '/inspection',
            [QCController::class, 'inspection']
        );

        Route::get(
            '/history',
            [QCController::class, 'history']
        );

        Route::post(
            '/store',
            [QCController::class, 'store']
        );
        Route::get('/edit/{id}', [QCController::class, 'edit']);
Route::put('/update/{id}', [QCController::class, 'update']);
    });
});

require __DIR__.'/auth.php';
