<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('titolo');
            $table->integer('anno')->nullable();
            $table->string('regista')->nullable();
            $table->string('categoria')->default('film');
            $table->text('descrizione')->nullable();
            $table->string('autore')->nullable();
            $table->timestamps();
        });

        // Tabella pivot per la relazione molti-a-molti con i Tag/Generi ($content->tags()->attach())
        Schema::create('content_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_tag');
        Schema::dropIfExists('contents');
    }
};