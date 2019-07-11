<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class A13FileGallery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
	        [Gallery]
        	컨트롤러: (UserController, LayoutController) GalleryController
        	모델: Gallery, Cadre
        	뷰: gallery
        */
        
	    $this->down();
	    
	    Schema::create('galleries', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->string('url')->nullable();
	        $table->longText('name')->nullable();
	        $table->longText('content')->nullable();
            $table->longText('allowed_group')->nullable();
            $table->longText('allowed_group_read')->nullable();
            $table->longText('allowed_group_cadre')->nullable();
	        $table->integer('layout')->nullable();
	        $table->string('skin')->nullable('default');
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->integer('count_cadre')->default('0');
	        $table->timestamps();
        });
	    
	    Schema::create('gallery_categories', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('gallery');
	        $table->longText('name')->nullable();
	        $table->integer('order_show');
	        $table->integer('state')->default('100');
	        $table->integer('count_cadre')->default('0');
	        $table->timestamps();
        });
	    
	    Schema::create('gallery_extravars', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('gallery');
	        $table->longText('name')->nullable();
	        $table->string('type');
	        $table->integer('order_show');
	        $table->longText('content')->nullable();
	        $table->integer('state')->default('100');
	        $table->timestamps();
        });
	    
	    Schema::create('gallery_cadres', function (Blueprint $table) {
	        $table->integer('id')->primary();
	        $table->integer('gallery');
	        $table->integer('category')->nullable();
	        $table->integer('author')->nullable();
	        $table->integer('order_show');
	        $table->longText('content')->nullable();
	        $table->integer('state')->default('100');
	        $table->integer('count_read')->default('0');
	        $table->integer('count_cadre')->default('0');
	        $table->string('ip_address')->nullable();
	        $table->timestamps();
        });
	    
	    Schema::create('gallery_cadre_extravars', function (Blueprint $table) {
	        $table->integer('extravar');
	        $table->integer('cadre');
	        $table->integer('gallery');
	        $table->longText('content')->nullable();
	        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        
        Schema::create('files', function (Blueprint $table) {
	        $table->integer('order_show')->nullable();
        });
        
        DB::table('modules')->insert([
	        'module'=>'gallery',
	        'name'=>'갤러리',
	        'order_group'=>4,
	        'order_show'=>3,
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
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('gallery_categories');
        Schema::dropIfExists('gallery_extravars');
        Schema::dropIfExists('gallery_cadres');
        Schema::dropIfExists('gallery_cadre_extravars');
        
        Schema::create('files', function (Blueprint $table) {
	        $table->dropColumn('order_show');
        });
        
        DB::table('modules')->where('module','gallery')->delete();
        DB::table('ids')->where('module','gallery')->delete();
    }
}
