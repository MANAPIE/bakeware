<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class EncryptionController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/encryption','EncryptionController@getAdminList');
	}
	
	static public function admin_menu(){
		if(!EncryptionController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/encryption',
				'name'=>'암호화 설정',
				'external'=>false,
				'current'=>'encryption',
				'submenu'=>null,
			],
		];
	}
	
	static public function admin_card(){
		return null;
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','encryption')->first()->manager;
		if(!$manager)
			if(array_key_exists(2,Auth::user()->groups())) // 관리 역할이 지정되지 않은 경우 모든 관리자가 권한 있음
				return true;
		else{
		    $allowed=false;
		    foreach(explode('|',$manager) as $group){
			    if(array_key_exists($group,Auth::user()->groups())){
				    $allowed=true;
				    break;
			    }
		    }
		    
		    if($allowed)
		    	return true;
	    }
		if(!$boolean) abort(401);
    	return false;
    }
    
    // 관리자 암호화 설정
	public function getAdminList(){
		Controller::logActivity('USR');
		EncryptionController::checkAuthority();
		View::share('current',['encryption',null]);
		
		return view('encryption.admin.list',['modules'=>DB::table('modules')->where('module','<>','encryption')->orderBy('order_group')->orderBy('order_show')->get()]);
	}
}
