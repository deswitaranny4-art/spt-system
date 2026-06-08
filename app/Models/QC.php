<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QC extends Model
{
    protected $table = 'qc';

    protected $fillable = [

        'docNumber',
        'supplier',
        'del_month',
        'del_year',
        'lineStop',
        'ng',
        'supply',
        'ppm',
        'ppmScore',
        'rank_score',
        'fppk',
        'total_score',
        'updated_by'
    ];
}