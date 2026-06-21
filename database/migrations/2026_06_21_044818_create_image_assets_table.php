<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_token', 80)->nullable()->index();
            $table->string('original_name');
            $table->string('category')->nullable();
            $table->string('background_category')->nullable();
            $table->unsignedBigInteger('background_asset_id')->nullable()->index();
            $table->boolean('background_removed')->default(false);
            $table->string('original_path');
            $table->string('processed_path')->nullable();
            $table->string('mime_type', 80)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('processed_format', 20)->nullable();
            $table->unsignedInteger('resize_width')->nullable();
            $table->unsignedInteger('resize_height')->nullable();
            $table->string('last_action', 40)->default('uploaded');
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('image_assets');
    }
}
