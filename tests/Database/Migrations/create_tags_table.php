<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('post_id');
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->unique(['tag_id', 'post_id']);
        });
    }
};