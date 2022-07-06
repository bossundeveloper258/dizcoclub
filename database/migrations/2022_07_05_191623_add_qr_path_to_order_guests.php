<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrPathToOrderGuests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_guests', function (Blueprint $table) {
            //
            $table->text('qr_path');
            $table->text('ticket')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_guests', function (Blueprint $table) {
            //
            $table->dropColumn('qr_path');
            $table->dropColumn('ticket');
        });
    }
}
