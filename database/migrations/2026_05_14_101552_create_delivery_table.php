<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        Schema::create('delivery', function (Blueprint $table) {

            $table->id();

            $table->string('docNumber');

            $table->string('supplierSearch');

            $table->string('createdOn')->nullable();

            $table->string('del_month');

            $table->string('del_year');

            $table->integer('otd')->default(0);

            $table->integer('qty_ord')->default(0);

            $table->integer('qty_rec')->default(0);

            $table->string('fulfillment')->nullable();

            $table->integer('del_method')->default(0);

            $table->bigInteger('premium')->default(0);

            $table->integer('dps')->default(0);

            $table->decimal('total_score', 5, 1)->default(0);

            $table->string('updatedBy')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery');
    }
};