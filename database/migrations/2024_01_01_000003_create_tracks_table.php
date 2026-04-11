<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksTable extends Migration
{
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('cover_art')->nullable();
            $table->enum('storage_type', ['local', 'amazon', 'cloudinary'])->default('local');
            $table->string('cloud_url')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->bigInteger('views')->default(0);
            $table->decimal('earnings', 10, 2)->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracks');
    }
}
