<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistsTable extends Migration
{
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stage_name')->unique();
            $table->text('bio')->nullable();
            $table->string('genre')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('total_views')->default(0);
            $table->integer('total_earnings')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('artists');
    }
}
