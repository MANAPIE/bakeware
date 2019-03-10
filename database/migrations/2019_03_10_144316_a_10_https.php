<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class A10Https extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
	        'id'=>'https',
	        'content'=>'N',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where([
	        'id'=>'https',
        ])->delete();
    }
}
