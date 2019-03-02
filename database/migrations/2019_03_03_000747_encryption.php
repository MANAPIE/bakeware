<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Encryption extends Migration
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
        	컨트롤러: EncryptionController
        	모델: SaferCrypto
        	뷰: encryption
        */
        
	    $this->down();
        
	    Schema::create('encryption_settings', function (Blueprint $table) {
            $table->string('module')->primary();
	        $table->integer('author')->nullable();
	        $table->boolean('encrypt')->default(0);
	        $table->timestamps();
        });
        
        DB::table('modules')->insert([
	        'module'=>'encryption',
	        'name'=>'암호화',
	        'order_group'=>0,
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
        Schema::dropIfExists('encryption_settings');
        
        DB::table('modules')->where('module','encryption')->delete();
    }
}
