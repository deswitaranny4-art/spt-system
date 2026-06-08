<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\QC;
use Illuminate\Support\Facades\DB;

class QCController extends Controller
{
    public function inspection()
    {
       $alreadyInspected = QC::pluck('docNumber')->toArray();
        $deliveries = Delivery::whereNotIn('docNumber', $alreadyInspected)->get();
        return view('qc.inspection', compact('deliveries'));
    }

    public function history()
{
    $qcData = QC::latest()->get();

    $approvedDocs = DB::table('approvals')
        ->where('status', 'APPROVED')
        ->pluck('doc_number')
        ->toArray();

    $inApprovalDocs = DB::table('approvals')
        ->pluck('doc_number')
        ->toArray();

    return view('qc.history', compact('qcData', 'approvedDocs', 'inApprovalDocs'));
}

public function edit($id)
{
    $user = auth()->user();
    if (!in_array($user->role, ['Admin', 'Staff'])) {
        return redirect()->back();
    }

    $qc = QC::findOrFail($id);

    $isApproved = DB::table('approvals')
        ->where('doc_number', $qc->docNumber)
        ->exists();

    if ($isApproved) {
        return redirect('/qc/history')
            ->with('error', 'Cannot edit - document is already in approval process.');
    }

    return view('qc.edit', compact('qc'));
}

public function update(Request $request, $id)
{
    $user = auth()->user();
    if (!in_array($user->role, ['Admin', 'Staff'])) {
        return redirect()->back();
    }

    $qc = QC::findOrFail($id);

    $isApproved = DB::table('approvals')
        ->where('doc_number', $qc->docNumber)
        ->exists();

    if ($isApproved) {
        return redirect('/qc/history')
            ->with('error', 'Cannot edit - document is already in approval process.');
    }

    $qc->update([
        'lineStop'    => $request->lineStop,
        'ng'          => $request->ng,
        'supply'      => $request->supply,
        'ppm'         => $request->ppm,
        'ppmScore'    => $request->ppmScore,
        'rank_score'  => $request->rank_score,
        'fppk'        => $request->fppk,
        'total_score' => $request->total_score,
        'updated_by'  => auth()->user()->name,
    ]);

    return redirect('/qc/history')
        ->with('success', 'QC updated successfully');
}

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'docNumber'   => 'nullable|string',
                'supplier'    => 'nullable|string',
                'del_month'   => 'nullable|string',
                'del_year'    => 'nullable|string',
                'lineStop'    => 'nullable|numeric',
                'ng'          => 'nullable|numeric',
                'supply'      => 'nullable|numeric',
                'ppm'         => 'nullable|numeric',
                'ppmScore'    => 'nullable|numeric',
                'rank_score'  => 'nullable|numeric',
                'fppk'        => 'nullable|numeric',
                'total_score' => 'nullable|numeric',
            ]);

            $validated['updated_by'] = auth()->user()->name ?? 'SYSTEM';

            QC::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'QC saved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}