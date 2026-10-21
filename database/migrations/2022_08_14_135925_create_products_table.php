<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // The prices are money, so they are kept with four decimals and
            // not as a float, which cannot hold a price exactly.
            $table->decimal('priceFrom', 13, 4);
            $table->decimal('priceTo', 13, 4);
            $table->enum('published', ['Yes', 'No']);
            $table->timestamps();
            $table->softDeletes();

            $table->index('published');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
