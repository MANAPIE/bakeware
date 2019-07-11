<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Basics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Admin]
        	컨트롤러: AdminController (이후 기재 시엔 생략)
        	모델: Setting
        	뷰: admin
        */
        
	    $this->down();
        
        Schema::create('sequence', function (Blueprint $table) {
            $table->increments('seq');
        });
        
        Schema::create('modules', function (Blueprint $table) {
            $table->string('module')->primary();
            $table->string('name');
            $table->integer('order_group')->default(0);
            $table->integer('order_show')->default(0);
            $table->longText('manager')->nullable();
            $table->timestamps();
        });
        
        Schema::create('ids', function (Blueprint $table) {
            $table->string('id')->default('')->primary();
            $table->string('module');
            $table->timestamp('created_at');
        });
        
        
        Schema::create('files', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('article')->nullable();
            $table->integer('author')->nullable();
            $table->string('original');
            $table->string('name');
            $table->string('extension')->nullable();
            $table->string('mime')->nullable();
            $table->integer('size');
            $table->string('type');
	        $table->integer('state')->default('100');
            $table->integer('count_download')->default('0');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
	    
        
        Schema::create('log_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('user')->nullable();
            $table->string('method')->nullable();
            $table->longText('message')->nullable();
            $table->longText('url')->nullable();
            $table->longText('referer')->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
	    
        Schema::create('log_error', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('code')->nullable();
            $table->string('method')->nullable();
            $table->longText('message')->nullable();
            $table->longText('description')->nullable();
            $table->longText('location')->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('author')->nullable();
            $table->integer('user')->nullable();
            $table->longText('message')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        
	    Schema::create('settings', function (Blueprint $table) {
	        $table->string('id')->primary();
	        $table->integer('author')->nullable();
	        $table->longText('content')->nullable();
	        $table->timestamps();
        });
        
        DB::table('settings')->insert([
	        'id'=>'app_name',
	        'content'=>'MANAPIE',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'app_description',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'app_preview',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'app_index',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'app_timezone',
	        'content'=>'Asia/Seoul',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_address',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'app_locale',
	        'content'=>'ko',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_host',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_port',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_username',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_password',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_encryption',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('settings')->insert([
	        'id'=>'mail_template',
	        'content'=>'default',
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
        Schema::dropIfExists('sequence');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('ids');
        
        Schema::dropIfExists('files');
        
        Schema::dropIfExists('log_error');
        Schema::dropIfExists('log_activity');
        
        Schema::dropIfExists('notifications');
        
        Schema::dropIfExists('settings');
    }
}
