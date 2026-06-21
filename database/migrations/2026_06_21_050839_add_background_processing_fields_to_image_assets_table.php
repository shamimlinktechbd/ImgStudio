<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackgroundProcessingFieldsToImageAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('image_assets', 'background_asset_id')) {
            return;
        }

        Schema::table('image_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('background_asset_id')->nullable()->index()->after('background_category');
            $table->boolean('background_removed')->default(false)->after('background_asset_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('image_assets', 'background_asset_id')) {
            return;
        }

        Schema::table('image_assets', function (Blueprint $table) {
            $table->dropColumn(['background_asset_id', 'background_removed']);
        });
    }
}
