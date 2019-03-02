<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class MenuController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/menu','MenuController@getAdminList');
		\Route::post('/admin/menu/create','MenuController@postAdminCreate');
		\Route::post('/admin/menu/edit','MenuController@postAdminEdit');
		\Route::post('/admin/menu/delete','MenuController@postAdminDelete');
	}
	
	static public function admin_menu(){
		if(!MenuController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/menu',
				'name'=>'메뉴 관리',
				'external'=>false,
				'current'=>'menu',
				'submenu'=>[],
			],
		];
	}
	
	static public function admin_card(){
		if(!MenuController::checkAuthority(true))
			return null;
			
		return null;
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','menu')->first()->manager;
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
    
    // 관리자 페이지 > 메뉴 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		MenuController::checkAuthority();
		View::share('current',['menu',null]);
		
		return view('menu.admin.list',['menus'=>\App\Menu::where('state',200)->orderBy('order_show')->get()]);
	}
    
    // 관리자 페이지 > 메뉴 관리
    // [POST] 새 메뉴판 만들기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		MenuController::checkAuthority();
		
		$id=Controller::getSequence();
		\App\Menu::create([
			'id'=>$id,
			'name'=>\App\Encryption::isEncrypt('menu')?\App\Encryption::encrypt($request->name):$request->name,
			'state'=>200,
			'order_show'=>\App\Menu::count()?\App\Menu::orderBy('order_show','desc')->first()->order_show+1:0,
		]);
		
		Controller::notify('<u>'.$request->name.'</u> 메뉴판을 만들었습니다.');
		return redirect()->back()->with('message'.$id,'새 메뉴판을 만들었습니다.');
	}
    
    // 관리자 페이지 > 메뉴 관리
    // [POST] 메뉴 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		MenuController::checkAuthority();
		
		$menu=\App\Menu::where(['id'=>$request->id,'state'=>200])->first();
		if(!$menu) abort(404);
		
		$menu->name=\App\Encryption::checkEncrypted($menu->name)?\App\Encryption::decrypt($menu->name):$menu->name;
		
		Controller::notify(($menu->name!=$request->menu_name?'<u>'.$menu->name.'</u> → ':'').'<u>'.$request->menu_name.'</u> 메뉴를 수정했습니다.');
		
		$menu->name=\App\Encryption::isEncrypt('menu')?\App\Encryption::encrypt($request->menu_name):$request->menu_name;
		$menu->save();
		
		\App\MenuItem::where('menu',$request->id)->delete();
		
		$parent=0;
		for($i=0;$i<count($request->name)-1;$i++){
			if($request->parent[$i]==0)
				$parent=0;
			
			\App\MenuItem::create([
				'menu'=>$request->id,
				'order_show'=>$i+1,
				'parent'=>$parent,
				'name'=>$request->name[$i]?(\App\Encryption::isEncrypt('menu')?\App\Encryption::encrypt($request->name[$i]):$request->name[$i]):'',
				'url'=>$request->url[$i]?(\App\Encryption::isEncrypt('menu')?\App\Encryption::encrypt($request->url[$i]):$request->url[$i]):'',
				'state'=>200,
				'external'=>$request->external[$i]??false,
			]);
			
			if($request->parent[$i]==0)
				$parent=$i+1;
		}
		return redirect()->back()->with(['edited'=>$request->id,'message'.$request->id=>'메뉴를 수정했습니다.']);
	}
    
    // 관리자 페이지 > 메뉴 관리
    // [POST] 메뉴 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		MenuController::checkAuthority();
		
		$menu=\App\Menu::where(['id'=>$request->id,'state'=>200])->first();
		if(!$menu) abort(404);
		
		$menu->state=400;
		$menu->save();
		\App\MenuItem::where('menu',$request->id)->update(['state'=>400]);
		
		$menu->name=\App\Encryption::checkEncrypted($menu->name)?\App\Encryption::decrypt($menu->name):$menu->name;
		
		Controller::notify('<u>'.$menu->name.'</u> 메뉴판을 삭제했습니다.');
		return redirect()->back()->with('message','메뉴판을 삭제했습니다.');
	}
	
}