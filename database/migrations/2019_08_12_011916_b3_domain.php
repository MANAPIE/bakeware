<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class B3Domain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('boards', function (Blueprint $table) {
	        $table->string('domain')->nullable()->after('url');
        });
        
        Schema::table('forms', function (Blueprint $table) {
	        $table->string('domain')->nullable()->after('url');
        });
        
        Schema::table('galleries', function (Blueprint $table) {
	        $table->string('domain')->nullable()->after('url');
        });
        
        Schema::table('pages', function (Blueprint $table) {
	        $table->string('domain')->nullable()->after('url');
        });
        
        Schema::table('page_onepages', function (Blueprint $table) {
	        $table->string('domain')->nullable()->after('url');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('boards', function (Blueprint $table) {
	        $table->dropColumn('domain');
        });
        
        Schema::table('forms', function (Blueprint $table) {
	        $table->dropColumn('domain');
        });
        
        Schema::table('galleries', function (Blueprint $table) {
	        $table->dropColumn('domain');
        });
        
        Schema::table('pages', function (Blueprint $table) {
	        $table->dropColumn('domain');
        });
        
        Schema::table('page_onepages', function (Blueprint $table) {
	        $table->dropColumn('domain');
        });
    }
}
