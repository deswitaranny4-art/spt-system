<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function input()
    {
        return view('delivery.input');
    }

public function history()
{
    $deliveries = Delivery::latest()->get();

    $approvedDocs = DB::table('approvals')
        ->where('status', 'APPROVED')
        ->pluck('doc_number')
        ->toArray();

    $inApprovalDocs = DB::table('approvals')
        ->pluck('doc_number')
        ->toArray();

    return view('delivery.history', compact('deliveries', 'approvedDocs', 'inApprovalDocs'));
}
public function edit($id)
{
    $user = auth()->user();
    if (!in_array($user->role, ['Admin', 'Staff'])) {
        return redirect()->back();
    }

    $delivery = Delivery::findOrFail($id);

    // Blok edit begitu ada di tabel approvals (sudah diapprove siapapun)
    $isApproved = DB::table('approvals')
        ->where('doc_number', $delivery->docNumber)
        ->exists();

    if ($isApproved) {
        return redirect('/delivery/history')
            ->with('error', 'Cannot edit - document is already in approval process.');
    }

    return view('delivery.edit', compact('delivery'));
}

public function update(Request $request, $id)
{
    $delivery = Delivery::findOrFail($id);

    $isApproved = DB::table('approvals')
        ->where('doc_number', $delivery->docNumber)
        ->exists();

    if ($isApproved) {
        return redirect('/delivery/history')
            ->with('error', 'Cannot edit - document is already in approval process.');
    }

    $delivery->update([
        'supplierSearch' => $request->supplierSearch,
        'del_month'      => $request->delMonth,
        'del_year'       => $request->delYear,
        'otd'            => $request->otd,
        'qty_ord'        => $request->qtyOrd,
        'qty_rec'        => $request->qtyRec,
        'fulfillment'    => $request->fulfillment,
        'del_method'     => $request->delMethod,
        'premium'        => $request->premium,
        'dps'            => $request->dps,
        'total_score'    => $request->totalScore,
        'updatedBy'      => auth()->user()->name,
    ]);

    return redirect('/delivery/history')
        ->with('success', 'Data updated successfully!');
}
    public function store(Request $request)
    {
        try{

            Delivery::create([

                'docNumber' =>
                    $request->docNumber,

                'supplierSearch' =>
                    $request->supplierSearch,

                'createdOn' =>
                    $request->createdOn,

                'del_month' =>
                    $request->delMonth,

                'del_year' =>
                    $request->delYear,

                'otd' =>
                    $request->otd,

                'qty_ord' =>
                    $request->qtyOrd,

                'qty_rec' =>
                    $request->qtyRec,

                'fulfillment' =>
                    $request->fulfillment,

                'del_method' =>
                    $request->delMethod,

                'premium' =>
                    $request->premium,

                'dps' =>
                    $request->dps,

                'total_score' =>
                    $request->totalScore,

                'updatedBy' =>
                    auth()->user()->name,
            ]);

            return response()->json([
                'success' => true
            ]);

        }catch(\Exception $e){

            return response()->json([

                'success' => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }
}