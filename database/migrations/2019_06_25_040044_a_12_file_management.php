<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class A12FileManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('files', function (Blueprint $table) {
			$table->timestamp('removed_at')->nullable();
	        $table->integer('order_show')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('files', function (Blueprint $table) {
			$table->dropColumn('removed_at');
			$table->dropColumn('order_show');
		});
    }
}
