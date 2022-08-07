<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlternativetoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alternativeto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('os')->nullable();
            $table->string('url')->nullable();
            $table->string('url_hash', 32)->nullable();
            $table->string('title')->nullable();
            $table->string('like')->nullable();
            $table->string('icon')->nullable();
            $table->string('anonce')->nullable();
            $table->string('company_website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alternativeto');
    }
}
