<?php

namespace App\Models;

use App\Models\SupplierReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class SupplierReport extends Model
{
    protected $table = 'supplier_reports';

    protected $fillable = [

        'doc_number',

        'supplier',

        'period',

        'qc_score',

        'delivery_score',

        'final_score'
    ];
}