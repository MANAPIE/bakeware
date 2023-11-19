<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Board extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Board]
        	컨트롤러: (UserController, LayoutController) BoardController
        	모델: Board, Document, Comment
        	뷰: board
        */
        
	    $this->down();
	    
	    Schema::create('boards', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->string('url')->nullable();
	        $table->longText('name')->nullable();
	        $table->longText('content')->nullable();
	        $table->string('sort_by')->default('created_at');
	        $table->string('sort_order')->default('desc');
            $table->longText('allowed_group')->nullable();
            $table->longText('allowed_group_read')->nullable();
            $table->longText('allowed_group_document')->nullable();
            $table->longText('allowed_group_comment')->nullable();
            $table->longText('allowed_group_mail')->nullable();
	        $table->integer('anonymous')->default(0); // 0: 익명 아님, 1: 관리자만 익명 아님, 2: 모두 익명
	        $table->integer('layout')->nullable();
	        $table->string('skin')->nullable('default');
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->integer('count_document')->default('0');
	        $table->integer('count_comment')->default('0');
	        $table->timestamps();
        });
	    
	    Schema::create('board_categories', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('board');
	        $table->longText('name')->nullable();
	        $table->integer('order_show');
	        $table->integer('state')->default('100');
	        $table->integer('count_document')->default('0');
	        $table->timestamps();
        });
	    
	    Schema::create('board_extravars', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('board');
	        $table->longText('name')->nullable();
	        $table->string('type');
	        $table->integer('order_show');
	        $table->longText('content')->nullable();
	        $table->integer('state')->default('100');
	        $table->timestamps();
        });
	    
	    Schema::create('board_documents', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('board');
	        $table->integer('category')->nullable();
	        $table->integer('author')->nullable();
	        $table->longText('title')->nullable();
	        $table->longText('content')->nullable();
	        $table->boolean('notice')->default(0);
	        $table->boolean('secret')->default(0);
	        $table->boolean('allow_comment')->default(1);
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->integer('count_comment')->default('0');
	        $table->string('ip_address')->nullable();
	        $table->timestamps();
        });
	    
	    Schema::create('board_document_extravars', function (Blueprint $table) {
	        $table->integer('extravar');
	        $table->integer('document');
	        $table->integer('board');
	        $table->longText('content')->nullable();
	        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
	    
	    Schema::create('board_document_comments', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('board');
	        $table->integer('document');
	        $table->integer('author')->nullable();
	        $table->longText('content')->nullable();
	        $table->boolean('notice')->default(0);
	        $table->boolean('secret')->default(0);
	        $table->integer('state')->default('100');
	        $table->string('ip_address')->nullable();
	        $table->timestamps();
        });
        
        DB::table('modules')->insert([
	        'module'=>'board',
	        'name'=>'게시판',
	        'order_group'=>4,
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
        Schema::dropIfExists('boards');
        Schema::dropIfExists('board_categories');
        Schema::dropIfExists('board_extravars');
        Schema::dropIfExists('board_documents');
        Schema::dropIfExists('board_document_extravars');
        Schema::dropIfExists('board_document_comments');
        
        DB::table('modules')->where('module','board')->delete();
        DB::table('ids')->where('module','board')->delete();
    }
}
