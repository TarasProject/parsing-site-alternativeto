<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->nullable();
            $table->string('url_hash', 32)->nullable();
            $table->string('title')->nullable();
            $table->string('page')->nullable();
            $table->string('like')->nullable();
            $table->string('icon')->nullable();
            $table->text('anonce')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->text('tags')->nullable();
            $table->string('platforms')->nullable();
            $table->string('count_alternatives')->nullable();
            $table->string('categories')->nullable();
            $table->text('sceenshots_urls')->nullable();
            $table->text('alternatives')->nullable();
            $table->text('license')->nullable();
            $table->text('app_stores')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('ratings')->nullable();
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
        Schema::dropIfExists('app');
    }
}
