<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc', function (Blueprint $table) {

            $table->id();

            $table->string('docNumber')->nullable();
            $table->string('supplier')->nullable();

            $table->string('del_month')->nullable();
            $table->string('del_year')->nullable();

            $table->integer('lineStop')->nullable();
            $table->integer('ng')->nullable();
            $table->integer('supply')->nullable();

            $table->float('ppm')->nullable();
            $table->float('ppmScore')->nullable();

            $table->float('rank_score')->nullable();

            $table->float('fppk')->nullable();

            $table->float('total_score')->nullable();

            $table->string('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc');
    }
};