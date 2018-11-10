<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PagesOnepage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Onepage]
        	컨트롤러: (PageController) OnepageController
        	모델: Onepage
        	뷰: onepage
        */
        
	    $this->down();
        
	    Schema::create('page_onepages', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->string('url')->nullable();
	        $table->string('name')->nullable();
	        $table->integer('layout')->nullable();
            $table->longText('allowed_group')->nullable();
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->timestamps();
        });
        
	    Schema::create('page_onepage_pages', function (Blueprint $table) {
	        $table->integer('onepage');
	        $table->integer('page');
	        $table->string('background')->nullable();
	        $table->integer('order_show');
	        $table->integer('state')->default('100');
	        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::table('modules')->insert([
	        'module'=>'onepage',
	        'name'=>'원페이지',
	        'order_group'=>3,
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
        Schema::dropIfExists('page_onepages');
        Schema::dropIfExists('page_onepage_pages');
        
        DB::table('modules')->where('module','onepage')->delete();
        DB::table('ids')->where('module','onepage')->delete();
    }
}
