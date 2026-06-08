<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class SupplierController extends Controller
{
    public function index()
    {
        $response = Http::withoutVerifying()
        ->get(
        'https://be-ams.sanohindonesia.co.id/api/public/supplier-data/suppliers'
    );

        if(!$response->successful()){

            return response()->json([
                'success' => false,
                'message' => 'API failed'
            ], 500);
        }

        return response()->json(
            $response->json()['data']
        );
    }
}