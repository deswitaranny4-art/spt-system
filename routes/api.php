<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/suppliers', function () {

    return [

        [
            "supplier_name" => "PT Supplier A"
        ],

        [
            "supplier_name" => "PT Supplier B"
        ],

        [
            "supplier_name" => "PT Supplier C"
        ]

    ];

});