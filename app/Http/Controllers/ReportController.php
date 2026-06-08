<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $qcData = DB::table('qc')->get();

        $deliveryData = DB::table('delivery')->get();

        $approvalData = DB::table('approvals')
            ->where('status', 'APPROVED')
            ->get();

        $historyData = DB::table('approval_histories')
            ->get()
            ->map(function ($h) {

                $user = DB::table('users')
                    ->where('name', $h->user_name)
                    ->first();

                return [
                    'doc_number'     => $h->doc_number,
                    'user_name'      => $h->user_name,
                    'role'           => $h->role_name,
                    'department'     => $h->department,
                    'action'         => $h->action,
                    'signature_path' => $user && $user->signature
                        ? asset('storage/' . $user->signature)
                        : null,
                ];
            });

        return view('report', [
            'qcData'       => $qcData,
            'deliveryData' => $deliveryData,
            'approvalData' => $approvalData,
            'historyData'  => $historyData,
        ]);
    }
}