<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class OnepageController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/page/onepage','OnepageController@getAdminList');
		\Route::get('/admin/page/onepage/create','OnepageController@getAdminCreate');
		\Route::post('/admin/page/onepage/create','OnepageController@postAdminCreate');
		\Route::get('/admin/page/onepage/{id}','OnepageController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/page/onepage/edit','OnepageController@postAdminEdit');
		\Route::post('/admin/page/onepage/delete','OnepageController@postAdminDelete');
	}
	
	static public function admin_menu(){
		if(!OnepageController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/page/onepage',
				'name'=>'원페이지 관리',
				'external'=>false,
				'current'=>'onepage',
				'submenu'=>[
					[
						'url'=>'/admin/page/onepage',
						'name'=>'원페이지 관리',
						'external'=>false,
						'current'=>null,
					],
					[
						'url'=>'/admin/page/onepage/create',
						'name'=>'원페이지 추가',
						'external'=>false,
						'current'=>'create',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!OnepageController::checkAuthority(true))
			return null;
			
		return [
			OnepageController::cardCountread(),
		];
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','onepage')->first()->manager;
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
    
    // 관리자 페이지 > 페이지 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		View::share('current',['onepage',null]);
		
		$query=\App\Onepage::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('onepage.admin.list',['pages'=>$query]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		View::share('current',['onepage','create']);
		
		return view('onepage.admin.create',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'pages'=>\App\Page::where('state',200)->orderBy('id','desc')->get()]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 추가
    // [POST] 새 페이지 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		
		$id=Controller::getSequence();
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		if(!$request->url) $request->url='';
		\App\Onepage::create([
			'id'=>$id,
			'url'=>$request->url,
			'name'=>\App\Encryption::isEncrypt('onepage')?\App\Encryption::encrypt($request->name):$request->name,
			'layout'=>$request->layout,
			'allowed_group'=>$group,
			'state'=>200,
		]);
		
		$page=\App\Onepage::find($id);
		
		for($i=0;$i<count($request->page);$i++){
			
			$original='background_original'.$i;
			$file=$request->file('background'.$i);
			if($request->hasFile('background'.$i))
				$background=ResourceController::saveFile('image',$file,$page->id);
			else
				$background=$request->$original??null;
				
			DB::table('page_onepage_pages')->insert([
				'onepage'=>$page->id,
				'page'=>$request->page[$i],
				'background'=>$background,
				'order_show'=>$i,
				'state'=>200,
			]);
		}
        
        DB::table('ids')->insert([
	        'id'=>$request->url,
	        'module'=>'onepage',
        ]);
		
		Controller::notify('<u>'.$request->name.'</u> 원페이지를 추가했습니다.');
		return redirect('/admin/page/onepage/'.$page->id)->with(['message'=>'페이지를 추가했습니다.']);
	}
    
    // 관리자 페이지 > 페이지 관리 > 페이지(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		View::share('current',['onepage',null]);
		
		$page=\App\Onepage::where(['id'=>$id,'state'=>200])->first();
		if(!$page) abort(404);
		
		return view('onepage.admin.create',['page'=>$page,'layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'pages'=>\App\Page::where('state',200)->orderBy('id','desc')->get()]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 페이지(보기)
    // [POST] 페이지 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		
		$page=\App\Onepage::where(['id'=>$request->id,'state'=>200])->first();
		if(!$page) abort(404);
        
        if($page->url!=$request->url){
	        DB::table('ids')->where([
		        'id'=>$page->url,
		        'module'=>'onepage',
	        ])->delete();
	        DB::table('ids')->insert([
		        'id'=>$request->url,
		        'module'=>'onepage',
	        ]);
	    }
		
		$page->name=\App\Encryption::checkEncrypted($page->name)?\App\Encryption::decrypt($page->name):$page->name;
		
		Controller::notify(($page->name!=$request->name?'<u>'.$page->name.'</u> → ':'').'<u>'.$request->name.'</u> 원페이지를 수정했습니다.');
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		$page->allowed_group=$group;
		if(!$request->url) $request->url='';
		$page->url=$request->url;
		$page->name=\App\Encryption::isEncrypt('onepage')?\App\Encryption::encrypt($request->name):$request->name;
		$page->layout=$request->layout;
		
		DB::table('page_onepage_pages')->where(['onepage'=>$page->id,'state'=>200])->delete();
		for($i=0;$i<count($request->page);$i++){
			
			$original='background_original'.$i;
			$file=$request->file('background'.$i);
			if($request->hasFile('background'.$i))
				$background=ResourceController::saveFile('image',$file,$page->id);
			else
				$background=$request->$original??null;
				
			DB::table('page_onepage_pages')->insert([
				'onepage'=>$page->id,
				'page'=>$request->page[$i],
				'background'=>$background,
				'order_show'=>$i,
				'state'=>200,
			]);
		}
		$page->save();
		
		return redirect('/admin/page/onepage/'.$page->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'페이지를 수정했습니다.']);
	}
    
    // 관리자 페이지 > 페이지 관리 > 수정(보기)
    // [POST] 페이지 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		OnepageController::checkAuthority();
		
		$page=\App\Onepage::where(['id'=>$request->id,'state'=>200])->first();
		if(!$page) abort(404);
		
        DB::table('ids')->where([
	        'id'=>$page->url,
	        'module'=>'onepage',
        ])->delete();
		
		$page->state=400;
		$page->save();
		
		$page->name=\App\Encryption::checkEncrypted($page->name)?\App\Encryption::decrypt($page->name):$page->name;
		
		Controller::notify('<u>'.$page->name.'</u> 원페이지를 삭제했습니다.');
		return redirect('/admin/page/onepage'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','페이지를 삭제했습니다.');
	}
	
	// 페이지 보기
	public function getList($url){
		Controller::logActivity('USR');
		
		$page=\App\Onepage::where(['url'=>$url,'state'=>200])->first();
		if(!$page) abort(404);
		
		if(count($page->groups())){
		    $allowed=false;
		    foreach($page->groups() as $group){
			    if(array_key_exists($group,Auth::user()->groups())){
				    $allowed=true;
				    break;
			    }
		    }
			if(!$allowed) abort(401);
		}
		
		$page->timestamps=false;
		$page->increment('count_read');
		
		return view('onepage.read',['layout'=>$page->layout?\App\Layout::find($page->layout):null,'page'=>$page]);
	}
	
	// 카드 - 방문 수
	static public function cardCountread(){
		$pages=\App\Onepage::where('state',200)->orderBy('count_read','desc')->limit(5)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/page').'">원페이지 방문 수</a></h4><ul>';
		foreach($pages as $page){
			$page->name=\App\Encryption::checkEncrypted($page->name)?\App\Encryption::decrypt($page->name):$page->name;
			$content.='<li><a href="'.url('/admin/page/onepage/'.$page->id).'">'.$page->name.'&nbsp;<span>'.$page->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
}