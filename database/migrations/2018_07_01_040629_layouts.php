<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Layouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Layout]
        	컨트롤러: (MenuController) LayoutController
        	모델: Layout
        	뷰: layout
        */
        
	    $this->down();
        
        Schema::create('layouts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->nullable();
            $table->integer('menu')->nullable();
            $table->string('path')->nullable();
            $table->integer('state')->default('100');
            $table->timestamps();
        });
        
        Schema::create('layouts_configs', function (Blueprint $table) {
            $table->integer('layout');
            $table->string('name');
            $table->string('type');
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        
        DB::table('modules')->insert([
	        'module'=>'layout',
	        'name'=>'레이아웃',
	        'order_group'=>1,
	        'order_show'=>1,
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
        Schema::dropIfExists('layouts');
        
        DB::table('modules')->where('module','layout')->delete();
    }
}
