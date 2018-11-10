<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//$mainRoute=function(){
		
	// 파일 처리
	\App\Http\Controllers\ResourceController::routes();
	
	// 최초의 설정(마이그레이션)이 안 되어있으면 settings 테이블이 없으므로 이걸로 최초의 설정 여부 검사함
	if(\Illuminate\Support\Facades\Schema::hasTable('settings')){
		// 로그인 관련
		Auth::routes();
		Route::get('/login',function(){return redirect('/admin');})->name('login');
		Route::get('/logout', 'Auth\LoginController@logout');
		
		// 관리자 페이지
		\App\Http\Controllers\AdminController::routes();
	    $modules=\DB::table('modules')->orderBy('order_group')->get();
	    foreach($modules as $module){
		    ('\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller')::routes();
	    }
		
		// 주소로 접속
		Route::get('/{url}/{id}/{action}', 'Controller@getActionFromUrlWithId')->where('action','create|complete|edit|delete|comment')->where('id','[0-9]+')->where('url','(.*)');
		Route::post('/{url}/{id}/{action}', 'Controller@postActionFromUrlWithId')->where('action','create|complete|edit|delete|comment')->where('id','[0-9]+')->where('url','(.*)');
		Route::get('/{url}/{action}', 'Controller@getActionFromUrl')->where('action','create|complete|edit|delete|comment')->where('url','(.*)');
		Route::post('/{url}/{action}', 'Controller@postActionFromUrl')->where('action','create|complete|edit|delete|comment')->where('url','(.*)');
		Route::get('/{url}/{id}', 'Controller@getReadFromUrl')->where('id','[0-9]+')->where('url','(.*)');
		Route::get('/{url}', 'Controller@getListFromUrl')->where('url','(.*)');
		
	}
	else{
	// 최초의 설정 되어있을 떄는 무조건 하도록 하는 페이지로 보냄
		Route::get('/{url}',function(){
			return view('error_initial');
		})->where('url','(.*)');
	}
//};

//Route::group(['domain'=>''], $mainRoute);