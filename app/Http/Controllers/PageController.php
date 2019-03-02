<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;
use File;
use Response;

class PageController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/page','PageController@getAdminList');
		\Route::get('/admin/page/create','PageController@getAdminCreate');
		\Route::post('/admin/page/create','PageController@postAdminCreate');
		\Route::get('/admin/page/{id}','PageController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/page/edit','PageController@postAdminEdit');
		\Route::post('/admin/page/delete','PageController@postAdminDelete');
		
		\Route::get('/page/image/{name}','PageController@getOuterImage')->where('name','(.*)');
	}
	
	static public function admin_menu(){
		if(!PageController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/page',
				'name'=>'페이지 관리',
				'external'=>false,
				'current'=>'page',
				'submenu'=>[
					[
						'url'=>'/admin/page/create',
						'name'=>'페이지 추가',
						'external'=>false,
						'current'=>'create',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!PageController::checkAuthority(true))
			return null;
			
		return [
			PageController::cardCountread(),
		];
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','page')->first()->manager;
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
		PageController::checkAuthority();
		View::share('current',['page',null]);
		
		$query=\App\Page::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->join('page_versions','pages.version','=','page_versions.id')->where('page_versions.title','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('pages.id','desc')->paginate(30);
		
		return view('page.admin.list',['pages'=>$query]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		PageController::checkAuthority();
		View::share('current',['page','create']);
		
		return view('page.admin.create',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 추가
    // [POST] 새 페이지 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		PageController::checkAuthority();
		
		$id=Controller::getSequence();
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		if(!$request->url) $request->url='';
		\App\Page::create([
			'id'=>$id,
			'url'=>$request->url,
			'type'=>$request->type,
			'allowed_group'=>$group,
			'state'=>200,
		]);
		
		$page=\App\Page::find($id);
		$version=$page->renewal((\App\Encryption::isEncrypt('page')?\App\Encryption::encrypt($request->name):$request->name),$request->layout,$request->type=='outer'?$request->content_outer:(\App\Encryption::isEncrypt('page')?\App\Encryption::encrypt($request->content_inner):$request->content_inner));
		$page->version=$version;
		$page->save();
        
        DB::table('ids')->insert([
	        'id'=>$request->url,
	        'module'=>'page',
        ]);
		
		Controller::notify('<u>'.$request->name.'</u> 페이지를 추가했습니다.');
		return redirect('/admin/page/'.$page->id)->with(['message'=>'페이지를 추가했습니다.']);
	}
    
    // 관리자 페이지 > 페이지 관리 > 페이지(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		PageController::checkAuthority();
		View::share('current',['page',null]);
		
		$page=\App\Page::where(['id'=>$id,'state'=>200])->first();
		if(!$page) abort(404);
		
		return view('page.admin.create',['page'=>$page,'layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
	}
    
    // 관리자 페이지 > 페이지 관리 > 페이지(보기)
    // [POST] 페이지 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		PageController::checkAuthority();
		
		$page=\App\Page::where(['id'=>$request->id,'state'=>200])->first();
		if(!$page) abort(404);
        
        if($page->url!=$request->url){
	        DB::table('ids')->where([
		        'id'=>$page->url,
		        'module'=>'page',
	        ])->delete();
	        DB::table('ids')->insert([
		        'id'=>$request->url,
		        'module'=>'page',
	        ]);
	    }
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		$page->allowed_group=$group;
		if(!$request->url) $request->url='';
		$page->url=$request->url;
		
		$content=($page->type=='outer'?$request->content_outer:$request->content_inner);
		
		Controller::notify(($page->name()!=$request->name?'<u>'.$page->name().'</u> → ':'').'<u>'.$request->name.'</u> 페이지를 수정했습니다.');
		
		if($page->name()!=$request->name||$page->layout()!=$request->layout||$page->content()!=$content){
			$request->name=\App\Encryption::isEncrypt('page')?\App\Encryption::encrypt($request->name):$request->name;
			$version=$page->renewal($request->name,$request->layout,($page->type=='outer'?$request->content_outer:(\App\Encryption::isEncrypt('page')?\App\Encryption::encrypt($request->content_inner):$request->content_inner)));
			$page->version=$version;
		}
		$page->save();
		
		return redirect('/admin/page/'.$page->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'페이지를 수정했습니다.']);
	}
    
    // 관리자 페이지 > 페이지 관리 > 수정(보기)
    // [POST] 페이지 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		PageController::checkAuthority();
		
		$page=\App\Page::where(['id'=>$request->id,'state'=>200])->first();
		if(!$page) abort(404);
		
        DB::table('ids')->where([
	        'id'=>$page->url,
	        'module'=>'page',
        ])->delete();
		
		$page->state=400;
		$page->save();
		
		Controller::notify('<u>'.$page->name().'</u> 페이지를 삭제했습니다.');
		return redirect('/admin/page'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','페이지를 삭제했습니다.');
	}
	
	// 페이지 보기
	public function getList($url){
		Controller::logActivity('USR');
		
		$page=\App\Page::where(['url'=>$url,'state'=>200])->first();
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
		
		return view('page.read',['layout'=>$page->layout()?\App\Layout::find($page->layout()):null,'page'=>$page]);
	}
	
	// 외부 페이지 이미지
	public function getOuterImage($name){
		$path=base_path().'/resources/views/page/outer/image/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		$response=Response::make($file,200);
	//	$response->withHeaders(['Content-Type'=>$type]);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	// 카드 - 방문 수
	static public function cardCountread(){
		$pages=\App\Page::where('state',200)->orderBy('count_read','desc')->limit(5)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/page').'">페이지 방문 수</a></h4><ul>';
		foreach($pages as $page){
			$content.='<li><a href="'.url('/admin/page/'.$page->id).'">'.$page->name().'&nbsp;<span>'.$page->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
}