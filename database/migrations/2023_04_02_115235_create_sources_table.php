<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("language")->nullable();
            $table->text("description")->nullable();
            $table->string("specialization")->nullable();
            $table->string("country")->nullable();
            $table->string("str_id")->nullable();
            $table->string("web_url")->nullable();
            $table->integer("data_source_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
