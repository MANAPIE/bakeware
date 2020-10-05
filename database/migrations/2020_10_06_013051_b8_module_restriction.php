<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class B8ModuleRestriction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	Schema::table('modules', function (Blueprint $table) {
        $table->boolean('active')->default(1);
    	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
  		Schema::table('modules', function (Blueprint $table) {
  			$table->dropColumn('active');
  		});
    }
}
