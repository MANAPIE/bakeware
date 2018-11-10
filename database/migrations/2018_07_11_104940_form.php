<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Form extends Migration
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
        	컨트롤러: (UserController, LayoutController) FormController
        	모델: Form, Answer
        	뷰: form
        */
        
	    $this->down();
	    
	    Schema::create('forms', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->string('url')->nullable();
	        $table->string('name')->nullable();
            $table->longText('allowed_group')->nullable();
            $table->longText('allowed_group_mail')->nullable();
	        $table->integer('layout')->nullable();
	        $table->string('skin')->nullable('default');
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->integer('count_question')->default('0');
	        $table->integer('count_answer')->default('0');
	        $table->timestamp('start_at')->nullable();
	        $table->timestamp('end_at')->nullable();
	        $table->timestamps();
        });
	    
	    Schema::create('form_questions', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('form');
	        $table->longText('name')->nullable();
	        $table->string('type');
	        $table->integer('order_show');
	        $table->longText('content')->nullable();
	        $table->integer('state')->default('100');
	        $table->timestamps();
        });
	    
	    Schema::create('form_answers', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('form');
	        $table->integer('author')->nullable();
	        $table->integer('state')->default('100');
	        $table->string('ip_address')->nullable();
	        $table->timestamps();
        });
	    
	    Schema::create('form_answer_items', function (Blueprint $table) {
	        $table->integer('question');
	        $table->integer('answer');
	        $table->integer('form');
	        $table->longText('content')->nullable();
	        $table->integer('state')->default('100');
	        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::table('modules')->insert([
	        'module'=>'form',
	        'name'=>'폼',
	        'order_group'=>4,
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
        Schema::dropIfExists('forms');
        Schema::dropIfExists('form_questions');
        Schema::dropIfExists('form_question_answers');
        
        DB::table('modules')->where('module','form')->delete();
        DB::table('ids')->where('module','form')->delete();
    }
}
