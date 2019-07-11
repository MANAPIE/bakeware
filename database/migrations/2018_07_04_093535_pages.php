<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Pages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Page]
        	컨트롤러: (UserController, LayoutController) PageController
        	모델: Page
        	뷰: page
        */
        
	    $this->down();
        
	    Schema::create('pages', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->string('url')->nullable();
	        $table->string('type')->default('inner');
	        $table->integer('version')->nullable();
            $table->longText('allowed_group')->nullable();
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->timestamps();
        });
        
	    Schema::create('page_versions', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('page');
	        $table->integer('author')->nullable();
	        $table->longText('title')->nullable();
	        $table->integer('layout')->nullable();
	        $table->longText('content')->nullable();
	        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::table('modules')->insert([
	        'module'=>'page',
	        'name'=>'페이지',
	        'order_group'=>3,
	        'order_show'=>1,
	        'manager'=>'',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        $page=\App\Http\Controllers\Controller::getSequence();
        $version=\App\Http\Controllers\Controller::getSequence();
        DB::table('pages')->insert([
	        'id'=>$page,
	        'url'=>'',
	        'type'=>'inner',
	        'version'=>$version,
			'state'=>200,
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('ids')->insert([
	        'id'=>'',
	        'module'=>'page',
        ]);
        
        /*
        DB::table('page_versions')->insert([
	        'id'=>$version,
	        'page'=>$page,
	        'author'=>4,
	        'title'=>'첫 페이지',
	        'layout'=>0,
	        'content'=>'<h2 style="text-align:center">환영합니다</h2>',
        ]);
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_versions');
        
        DB::table('modules')->where('module','page')->delete();
        DB::table('ids')->where('module','page')->delete();
    }
}
