<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Menus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Menu]
        	컨트롤러: MenuController
        	모델: Menu, MenuItem
        	뷰:
        */
        
	    $this->down();
        
        Schema::create('menus', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->nullable();
            $table->integer('order_show');
            $table->integer('state')->default('100');
            $table->timestamps();
        });
        
        Schema::create('menus_items', function (Blueprint $table) {
            $table->integer('menu');
            $table->integer('order_show');
            $table->integer('parent')->nullable();
            $table->string('name')->default('');
            $table->string('url')->default('');
            $table->boolean('external')->default(false);
            $table->integer('state')->default('100');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::table('modules')->insert([
	        'module'=>'menu',
	        'name'=>'메뉴',
	        'order_group'=>1,
	        'order_show'=>2,
	        'manager'=>'',
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
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menus_items');
        
        DB::table('modules')->where('module','menu')->delete();
    }
}
