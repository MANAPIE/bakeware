<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [User]
        	컨트롤러: UserController
        	모델: User
        	뷰: user
        */
        
	    $this->down();
	    
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->string('nickname');
            $table->string('password');
            $table->string('email')->nullable();
            $table->integer('state')->default('100');
            $table->longText('note')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
	    
        Schema::create('users_group', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->integer('state')->default('100');
            $table->timestamps();
        });
	    
        Schema::create('users_groups', function (Blueprint $table) {
            $table->integer('user');
            $table->integer('group');
            $table->timestamp('created_at');
        });
        
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        
        DB::table('modules')->insert([
	        'module'=>'user',
	        'name'=>'회원',
	        'order_group'=>2,
	        'order_show'=>1,
	        'manager'=>'',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        // 기본 회원 그룹 생성
        
        $master=\App\Http\Controllers\Controller::getSequence();
        DB::table('users_group')->insert([
	        'id'=>1,
	        'name'=>'마스터',
	        'state'=>200,
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        $administrator=\App\Http\Controllers\Controller::getSequence();
        DB::table('users_group')->insert([
	        'id'=>2,
	        'name'=>'관리자',
	        'state'=>200,
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        $member=\App\Http\Controllers\Controller::getSequence();
        DB::table('users_group')->insert([
	        'id'=>3,
	        'name'=>'회원',
	        'state'=>200,
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        // 최초의 마스터 회원 생성
        
        $user=\App\Http\Controllers\Controller::getSequence();
        DB::table('users')->insert([
	        'id'=>$user,
	        'name'=>'manapie',
	        'nickname'=>'마나파이',
	        'email'=>'developer@manapie.me',
			'state'=>200,
	        'password'=>\Hash::make('developer@manapie.me'),
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('users_groups')->insert([
	        'user'=>$user,
	        'group'=>1,
        ]);
        
        DB::table('users_groups')->insert([
	        'user'=>$user,
	        'group'=>2,
        ]);
        
        DB::table('users_groups')->insert([
	        'user'=>$user,
	        'group'=>3,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('users_group');
        Schema::dropIfExists('users_groups');
        
        Schema::dropIfExists('password_resets');
        
        DB::table('modules')->where('module','user')->delete();
    }
}
