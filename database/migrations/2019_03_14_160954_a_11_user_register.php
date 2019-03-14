<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class A11UserRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('user_settings', function (Blueprint $table) {
	        $table->string('id')->primary();
	        $table->integer('author')->nullable();
	        $table->longText('content')->nullable();
	        $table->timestamps();
        });
        
        DB::table('user_settings')->insert([
	        'id'=>'allow_register',
	        'content'=>'N',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'layout',
	        'content'=>0,
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'skin',
	        'content'=>'default',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'auto_register',
	        'content'=>'N',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'first_groups',
	        'content'=>'3',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'term_service',
	        'content'=>'<p>환영합니다.</p>',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
        DB::table('user_settings')->insert([
	        'id'=>'term_privacy',
	        'content'=>'',
	        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
	        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
        ]);
        
	    Schema::create('user_setting_terms', function (Blueprint $table) {
	        $table->string('term');
	        $table->integer('author')->nullable();
	        $table->longText('content')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        DB::table('user_setting_terms')->insert([
	        'term'=>'service',
	        'content'=>'<p>환영합니다.</p>',
        ]);
        
        DB::table('user_setting_terms')->insert([
	        'term'=>'pricacy',
	        'content'=>'',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('user_setting_terms');
    }
}
