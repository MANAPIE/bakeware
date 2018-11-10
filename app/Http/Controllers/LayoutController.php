<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class LayoutController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/layout','LayoutController@getAdminList');
		\Route::post('/admin/layout/create','LayoutController@postAdminCreate');
		\Route::post('/admin/layout/edit','LayoutController@postAdminEdit');
		\Route::post('/admin/layout/delete','LayoutController@postAdminDelete');
	}
	
	static public function admin_menu(){
		if(!LayoutController::checkAuthority(true))
			return null;
			
		return [
			[
				'url'=>'/admin/layout',
				'name'=>'레이아웃 관리',
				'external'=>false,
				'current'=>'layout',
				'submenu'=>[],
			],
		];
	}
	
	static public function admin_card(){
		if(!LayoutController::checkAuthority(true))
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
		
		$manager=DB::table('modules')->where('module','layout')->first()->manager;
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
	
    // 관리자 페이지 > 레이아웃 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		LayoutController::checkAuthority();
		View::share('current',['layout',null]);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/layout/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/layout/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('layout.admin.list',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'menus'=>\App\Menu::where('state',200)->orderBy('order_show')->get(),'paths'=>$paths]);
	}
    
    // 관리자 페이지 > 레이아웃 관리
    // [POST] 새 레이아웃 만들기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		LayoutController::checkAuthority();
		
		$id=Controller::getSequence();
		\App\Layout::create([
			'id'=>$id,
			'name'=>$request->name,
			'menu'=>$request->menu0,
			'path'=>$request->path0,
			'state'=>200,
		]);
		
		Controller::notify('<u>'.$request->name.'</u> 레이아웃을 만들었습니다.');
		return redirect()->back()->with('message'.$id,'새 레이아웃을 만들었습니다.');
	}
    
    // 관리자 페이지 > 레이아웃 관리
    // [POST] 레이아웃 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		LayoutController::checkAuthority();
		
		$layout=\App\Layout::where(['id'=>$request->id,'state'=>200])->first();
		if(!$layout) abort(404);
		
		Controller::notify(($layout->name!=$request->layout_name?'<u>'.$layout->name.'</u> → ':'').'<u>'.$request->layout_name.'</u> 레이아웃을 수정했습니다.');
		
		$layout->name=$request->layout_name;
		$layout->menu=$request->{'menu'.$request->id};
		$layout->save();
		
		if($layout->configs()){
			foreach($layout->configs() as $config){
				$input='config_'.$config->name;
				$original='config_'.$config->name.'_original';
				if($config->type=='image'){
					$file=$request->file($input);
					if($request->hasFile($input))
						$request->$input=ResourceController::saveFile('image',$file,$layout->id);
					else
						$request->$input=$request->$original??null;
				}
				
				if(!$layout->hasConfig($config->name)){
					DB::table('layouts_configs')->insert([
						'layout'=>$layout->id,
						'name'=>$config->name,
						'type'=>$config->name,
						'value'=>$request->$input,
						'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
						'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					]);
				}else{
					DB::table('layouts_configs')->where([
						'layout'=>$layout->id,
						'name'=>$config->name,
					])->update([
						'type'=>$config->name,
						'value'=>$request->$input,
						'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
					]);
				}
			}
			
		}else{
			DB::table('layouts_configs')->where(['layout'=>$layout->id])->delete();
		}
		
		return redirect()->back()->with(['edited'=>$request->id,'message'.$request->id=>'레이아웃을 수정했습니다.']);
	}
    
    // 관리자 페이지 > 레이아웃 관리
    // [POST] 레이아웃 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		LayoutController::checkAuthority();
		
		$layout=\App\Layout::where(['id'=>$request->id,'state'=>200])->first();
		if(!$layout) abort(404);
		
		$layout->state=400;
		$layout->save();
		
		Controller::notify('<u>'.$layout->name.'</u> 레이아웃을 삭제했습니다.');
		return redirect()->back()->with('message','레이아웃을 삭제했습니다.');
	}
}