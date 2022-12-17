<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->string('title');
            $table->string('slug')->primary();
            $table->text('body');
            $table->string('extra')->nullable();
            $table->timestamps();
        });
    }
};
