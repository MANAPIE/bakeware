<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;
use File;
use Response;

class GalleryController extends Controller {
	
	static public function routes(){
		\Route::get('/gallery/{skin}/image/{name}','GalleryController@getImageResource')->where('name','(.*)');
		\Route::get('/gallery/{skin}/style/{name}','GalleryController@getStyleResource')->where('name','(.*)');
		\Route::get('/gallery/{skin}/font/{name}','GalleryController@getFontResource')->where('name','(.*)');
		\Route::get('/gallery/{skin}/script/{name}','GalleryController@getScriptResource')->where('name','(.*)');
		
		\Route::get('/admin/gallery','GalleryController@getAdminList');
		\Route::get('/admin/gallery/create','GalleryController@getAdminCreate');
		\Route::post('/admin/gallery/create','GalleryController@postAdminCreate');
		\Route::get('/admin/gallery/{id}','GalleryController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/gallery/edit','GalleryController@postAdminEdit');
		\Route::post('/admin/gallery/delete','GalleryController@postAdminDelete');
		
		\Route::get('/admin/gallery/{id}/cadres','GalleryController@getAdminCadresList')->where('id','[0-9]+');
		\Route::get('/admin/gallery/{id}/cadres/create','GalleryController@getAdminCadresCreate')->where('id','[0-9]+');
		\Route::post('/admin/gallery/cadres/create','GalleryController@postAdminCadresCreate');
		\Route::get('/admin/gallery/{id}/cadres/{cadre}','GalleryController@getAdminCadresEdit')->where('id','[0-9]+')->where('cadre','[0-9]+');
		\Route::post('/admin/gallery/cadres/edit','GalleryController@postAdminCadresEdit');
		\Route::post('/admin/gallery/cadres/delete','GalleryController@postAdminCadresDelete');
		\Route::post('/admin/gallery/cadres/order','GalleryController@postAdminCadresOrder');
	}
	
	static public function admin_menu(){
		if(!GalleryController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/gallery',
				'name'=>'갤러리 관리',
				'external'=>false,
				'current'=>'gallery',
				'submenu'=>[
					[
						'url'=>'/admin/gallery',
						'name'=>'갤러리 관리',
						'external'=>false,
						'current'=>null,
					],
					[
						'url'=>'/admin/gallery/create',
						'name'=>'갤러리 추가',
						'external'=>false,
						'current'=>'create',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!GalleryController::checkAuthority(true))
			return null;
			
		return [
			GalleryController::cardCountread(),
			GalleryController::cardCountcadre(),
			GalleryController::cardCountreadCadre(),
		];
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','gallery')->first()->manager;
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
	
	public function getImageResource($skin,$name){
		$path=base_path().'/resources/views/gallery/'.$skin.'/_image/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;

	}
	
	public function getStyleResource($skin,$name){
		$path=base_path().'/resources/views/gallery/'.$skin.'/_style/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type]);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	public function getFontResource($skin,$name){
		$path=base_path().'/resources/views/gallery/'.$skin.'/_style/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		if($type=='text/plain')
			$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=31536000']);
		return $response;
	}
	
	public function getScriptResource($skin,$name){
		$path=base_path().'/resources/views/gallery/'.$skin.'/_script/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/javascript';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
    
    // 관리자 갤러리 > 갤러리 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery',null]);
		
		$query=\App\Gallery::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('gallery.admin.list',['galleries'=>$query]);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery','create']);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/gallery/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/gallery/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('gallery.admin.create',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 추가
    // [POST] 새 갤러리 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		
		$id=Controller::getSequence();
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		if(!$request->group_read) $request->group_read=[];
		$group_read=implode('|',$request->group_read);
		if(!$request->group_cadre) $request->group_cadre=[];
		$group_cadre=implode('|',$request->group_cadre);
		if(!$request->url) $request->url='';
		\App\Gallery::create([
			'id'=>$id,
			'url'=>$request->url,
			'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->name):$request->name,
			'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->content):$request->content,
			'allowed_group'=>$group,
			'allowed_group_read'=>$group_read,
			'allowed_group_cadre'=>$group_cadre,
			'layout'=>$request->layout,
			'skin'=>$request->skin,
			'state'=>200,
		]);
		
		$gallery=\App\Gallery::find($id);
		
		for($i=0;$i<count($request->category)-1;$i++){
			DB::table('gallery_categories')->insert([
				'id'=>Controller::getSequence(),
				'gallery'=>$id,
				'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
				'order_show'=>$i,
				'state'=>200,
				'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
				'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			]);
		}
		
		for($i=0;$i<count($request->extravar)-1;$i++){
			DB::table('gallery_extravars')->insert([
				'id'=>Controller::getSequence(),
				'gallery'=>$id,
				'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
				'type'=>$request->extravar_type[$i],
				'order_show'=>$i,
				'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
				'state'=>200,
				'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
				'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			]);
		}
        
        DB::table('ids')->insert([
	        'id'=>$request->url,
	        'module'=>'gallery',
        ]);
		
		Controller::notify('<u>'.$request->name.'</u> 갤러리를 추가했습니다.');
		return redirect('/admin/gallery/'.$gallery->id)->with(['message'=>'갤러리를 추가했습니다.']);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 갤러리(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery',null]);
		
		$gallery=\App\Gallery::where(['id'=>$id,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/gallery/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/gallery/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('gallery.admin.create',['gallery'=>$gallery,'layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 갤러리(보기)
    // [POST] 갤러리 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		
		$gallery=\App\Gallery::where(['id'=>$request->id,'state'=>200])->first();
		if(!$gallery) abort(404);
        
        if($gallery->url!=$request->url){
	        DB::table('ids')->where([
		        'id'=>$gallery->url,
		        'module'=>'gallery',
	        ])->delete();
	        DB::table('ids')->insert([
		        'id'=>$request->url,
		        'module'=>'gallery',
	        ]);
	    }
	    
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		$gallery->allowed_group=$group;
		if(!$request->group_read) $request->group_read=[];
		$group_read=implode('|',$request->group_read);
		$gallery->allowed_group_read=$group_read;
		if(!$request->group_cadre) $request->group_cadre=[];
		$group_cadre=implode('|',$request->group_cadre);
		$gallery->allowed_group_cadre=$group_cadre;
		if(!$request->url) $request->url='';
		
		$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
		
		Controller::notify(($gallery->name!=$request->name?'<u>'.$gallery->name.'</u> → ':'').'<u>'.$request->name.'</u> 갤러리를 수정했습니다.');
		
		$gallery->url=$request->url;
		$gallery->name=\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->name):$request->name;
		$gallery->content=\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->content):$request->content;
		$gallery->layout=$request->layout;
		$gallery->skin=$request->skin;
		
		$gallery->save();
		
		DB::table('gallery_categories')->where(['gallery'=>$gallery->id,'state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->category)-1;$i++){
			if(!$request->category[$i]){
				DB::table('gallery_categories')->insert([
					'id'=>Controller::getSequence(),
					'gallery'=>$gallery->id,
					'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
					'order_show'=>$i,
					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('gallery_categories')->where(['gallery'=>$gallery->id,'id'=>$request->category[$i]])->update([
					'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
					'order_show'=>$i,
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
		
		DB::table('gallery_extravars')->where(['gallery'=>$gallery->id,'state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->extravar)-1;$i++){
			if(!$request->extravar[$i]){
				DB::table('gallery_extravars')->insert([
					'id'=>Controller::getSequence(),
					'gallery'=>$gallery->id,
					'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('gallery_extravars')->where(['gallery'=>$gallery->id,'id'=>$request->extravar[$i]])->update([
					'name'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
		
		return redirect('/admin/gallery/'.$gallery->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'갤러리를 수정했습니다.']);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 수정(보기)
    // [POST] 갤러리 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		
		$gallery=\App\Gallery::where(['id'=>$request->id,'state'=>200])->first();
		if(!$gallery) abort(404);
		
        DB::table('ids')->where([
	        'id'=>$gallery->url,
	        'module'=>'gallery',
        ])->delete();
        
        DB::table('gallery_cadres')->where(['gallery'=>$gallery->id,'state'=>200])->update(['state'=>410]);
        // 41x은 상위 오브젝트가 사라지면서 삭제된 경우
		
		$gallery->state=400;
		$gallery->save();
		
		$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
		
		Controller::notify('<u>'.$gallery->name.'</u> 갤러리를 삭제했습니다.');
		return redirect('/admin/gallery'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','갤러리를 삭제했습니다.');
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 액자 목록
	public function getAdminCadresList($id){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery',null]);
		
		$gallery=\App\Gallery::where(['id'=>$id,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		return view('gallery.admin.cadre_list',['gallery'=>$gallery]);
	}
    
    // 관리자 갤러리 > 갤러리 관리 > 액자 만들기
	public function getAdminCadresCreate($id){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery',null]);
		
		$gallery=\App\Gallery::where(['id'=>$id,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		return view('gallery.admin.cadre_create',['gallery'=>$gallery]);
	}
	
    // 관리자 갤러리 > 갤러리 관리 > 액자 만들기
    // [POST] 액자 만들기
	public function postAdminCadresCreate(Request $request){
		$gallery=\App\Gallery::where(['id'=>$request->gallery,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		return $this->postCreate($request,$gallery->url,true);
	}
	
    // 관리자 갤러리 > 갤러리 관리 > 액자 수정
	public function getAdminCadresEdit($id,$cadre){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		View::share('current',['gallery',null]);
		
		$gallery=\App\Gallery::where(['id'=>$id,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$cadre,'state'=>200])->first();
		if(!$cadre) abort(404);
		
		return view('gallery.admin.cadre_create',['gallery'=>$gallery,'cadre'=>$cadre]);
	}
	
    // 관리자 갤러리 > 갤러리 관리 > 액자 수정
    // [POST] 액자 수정
	public function postAdminCadresEdit(Request $request){
		$gallery=\App\Gallery::where(['id'=>$request->gallery,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$request->id,'state'=>200])->first();
		if(!$cadre) abort(404);
		
		return $this->postEdit($request,$gallery->url,$cadre->id,true);
	}
	
    // 관리자 갤러리 > 갤러리 관리 > 액자 목록
    // [POST] 액자 삭제
	public function postAdminCadresDelete(Request $request,$fromGallery=true){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		
		foreach($request->cadres as $id){
			$cadre=\App\Cadre::where(['id'=>$id,'state'=>200])->first();
			$cadre->timestamps=false;
			$cadre->state=401;
			$cadre->save();
			$gallery=$cadre->gallery();
			$gallery->timestamps=false;
			$gallery->decrement('count_cadre');
		}
        // 401은 관리자에 의해 삭제된 경우
		
		Controller::notify('액자를 일괄 삭제했습니다.');
		return redirect(($fromGallery?'/admin/gallery/'.$request->gallery.'/cadres':'/admin/gallery/cadre').($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','액자를 삭제했습니다.');
	}
	
    // 관리자 갤러리 > 갤러리 관리 > 액자 목록
    // [POST] 액자 순서
	public function postAdminCadresOrder(Request $request){
		Controller::logActivity('USR');
		GalleryController::checkAuthority();
		
		$i=0;
		foreach(array_reverse($request->cadres) as $id){
			$cadre=\App\Cadre::where(['id'=>$id,'state'=>200])->first();
			$cadre->timestamps=false;
			$cadre->order_show=$i;
			$cadre->save();
			$i++;
		}
		
		Controller::notify('액자 순서를 조정했습니다.');
		return redirect('/admin/gallery/'.$request->gallery.'/cadres'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','액자 순서를 조정했습니다.');
	}
	
	// 갤러리 목록
	public function getList($url){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		$gallery->timestamps=false;
		$gallery->increment('count_read');
		
		if(!$gallery->authority()) return $this->getCreate($url);
		
		return view('gallery.'.$gallery->skin.'.list',['layout'=>$gallery->layout?\App\Layout::find($gallery->layout):null,'gallery'=>$gallery]);
	}
	
	// 액자 보기
	public function getRead($url,$id){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$gallery->authority('read')) abort(401);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$id,'state'=>200])->first();
		if(!$cadre) abort(404);
		
		$cadre->timestamps=false;
		$cadre->increment('count_read');
		
		return view('gallery.'.$gallery->skin.'.read',['layout'=>$gallery->layout?\App\Layout::find($gallery->layout):null,'gallery'=>$gallery,'cadre'=>$cadre]);
	}
	
	// 액자 만들기
	public function getCreate($url){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$gallery->authority('cadre')) abort(401);
		
		return view('gallery.'.$gallery->skin.'.create',['layout'=>$gallery->layout?\App\Layout::find($gallery->layout):null,'gallery'=>$gallery]);
	}
	
	// 액자 만들기
	// [POST] 액자 만들기
	public function postCreate(Request $request,$url,$fromAdmin=false){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$fromAdmin)
			if(!$gallery->authority('cadre')) abort(401);
		
		$id=Controller::getSequence();
		
		\App\Cadre::create([
			'id'=>$id,
			'gallery'=>$gallery->id,
			'category'=>$request->category,
			'author'=>Auth::check()?Auth::user()->id:null,
			'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->content):$request->content,
			'order_show'=>$gallery->count_cadre,
			'state'=>200,
			'ip_address'=>$request->ip(),
		]);
		
		if(count($gallery->extravars())){
			foreach($gallery->extravars() as $extravar){
				$input='extravar'.$extravar->id;
				if($extravar->type=='checkbox'||$extravar->type=='order'){
					$content=$request->$input?implode('|',$request->$input):null;
				}else{
					if($extravar->type=='file'||$extravar->type=='image'){
						$original='extravar'.$extravar->id.'_original';
						$file=$request->file($input);
						if($request->hasFile($input))
							$request->$input=ResourceController::saveFile($extravar->type,$file,$id);
						else
							$request->$input=$request->$original??null;
					}
					
					$content=$request->$input;
				}
				
				DB::table('gallery_cadre_extravars')->insert([
					'extravar'=>$extravar->id,
					'cadre'=>$id,
					'gallery'=>$gallery->id,
					'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}
		
		if(null!==$request->attach_dropzone)
			foreach($request->attach_dropzone as $attach)
				DB::table('files')->where('name',$attach)->update(['article'=>$id]);
		
		$gallery->timestamps=false;
		$gallery->increment('count_cadre');
		
		if($fromAdmin)
			$redirect='/admin/gallery/'.$gallery->id.'/cadres';
		else 
			if($gallery->authority('read'))
				$redirect='/'.$gallery->url.'/'.$id;
			else
				$redirect='/'.$gallery->url.'/complete';
		
		$cadre=\App\Cadre::find($id);
		
		$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
		
		Controller::notify('<u>'.$gallery->name.'</u> 갤러리에 액자를 만들었습니다.');
		return redirect($redirect.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
	
	// 액자 만들기 - 액자 열람 권한 없을 때 오는 작성 완료 페이지
	public function getComplete($url){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		
		return view('gallery.'.$gallery->skin.'.complete',['layout'=>$gallery->layout?\App\Layout::find($gallery->layout):null,'gallery'=>$gallery]);
	}
	
	// 액자 수정
	public function getEdit($url,$id){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$gallery->authority('cadre')) abort(401);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$id,'state'=>200])->first();
		if(!$cadre) abort(404);
		if(!$cadre->isMine()) abort(404);
		
		return view('gallery.'.$gallery->skin.'.create',['layout'=>$gallery->layout?\App\Layout::find($gallery->layout):null,'gallery'=>$gallery,'cadre'=>$cadre]);
	}
	
	// 액자 수정
	// [POST] 액자 수정
	public function postEdit(Request $request,$url,$id,$fromAdmin=false){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$fromAdmin)
			if(!$gallery->authority('cadre')) abort(401);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$id,'state'=>200])->first();
		if(!$cadre) abort(404);
		if(!$fromAdmin)
			if(!$cadre->isMine()) abort(404);
		
		$cadre->category=$request->category;
		$cadre->content=\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($request->content):$request->content;
		$cadre->save();
		
		DB::table('gallery_cadre_extravars')->where('cadre',$cadre->id)->delete();
		if(count($gallery->extravars())){
			foreach($gallery->extravars() as $extravar){
				$input='extravar'.$extravar->id;
		
				if($extravar->type=='checkbox'||$extravar->type=='order'){
					$content=$request->$input?implode('|',$request->$input):null;
				}else{
					if($extravar->type=='file'||$extravar->type=='image'){
						$original='extravar'.$extravar->id.'_original';
						$file=$request->file($input);
						if($request->hasFile($input))
							$request->$input=ResourceController::saveFile($extravar->type,$file,$id);
						else
							$request->$input=$request->$original??null;
					}
					
					$content=$request->$input;
				}
				
				DB::table('gallery_cadre_extravars')->insert([
					'extravar'=>$extravar->id,
					'cadre'=>$id,
					'gallery'=>$gallery->id,
					'content'=>\App\Encryption::isEncrypt('gallery')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}
		
		if(null!==$request->attach_dropzone){
			if($cadre->files())
				foreach($cadre->files() as $attach){
					if(!in_array($attach->name,$request->attach_dropzone)){
						\Storage::delete($attach->name);
						DB::table('files')->where('name',$attach->name)->update(['state'=>400]);
					}
				}
		
			$i=0;
			foreach($request->attach_dropzone as $attach){
				DB::table('files')->where('name',$attach)->update(['article'=>$id,'order_show'=>$i]);
				$i++;
			}
		}else{
			if($cadre->files())
				foreach($cadre->files() as $attach){
					\Storage::delete($attach->name);
					DB::table('files')->where('name',$attach->name)->update(['state'=>400]);
				}
		}
		
		if($fromAdmin)
			$redirect='/admin/gallery/'.$gallery->id.'/cadres';
		else 
			if($gallery->authority('read'))
				$redirect='/'.$gallery->url.'/'.$id;
			else
				$redirect='/'.$gallery->url.'/complete';
				
		$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
		
		Controller::notify('<u>'.$gallery->name.'</u> 갤러리의 액자를 수정했습니다.');
		return redirect($redirect.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
    
    // 액자 삭제
    // [POST] 액자 삭제
	public function postDelete(Request $request,$url,$id){
		Controller::logActivity('USR');
		
		$gallery=\App\Gallery::where(['url'=>$url,'state'=>200])->first();
		if(!$gallery) abort(404);
		if(!$gallery->authority('cadre')) abort(401);
		
		$cadre=\App\Cadre::where(['gallery'=>$gallery->id,'id'=>$id,'state'=>200])->first();
		if(!$cadre) abort(404);
		if(!$cadre->isMine()) abort(404);
		
		$cadre->state=400;
		$cadre->save();
		
		$gallery->timestamps=false;
		$gallery->decrement('count_cadre');
		
		$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
		
		Controller::notify('<u>'.$gallery->name.'</u> 갤러리의 액자를 삭제했습니다.');
		return redirect('/'.$gallery->url.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','액자를 삭제했습니다.');
	}
	
	// 카드 - 갤러리 방문 수
	static public function cardCountread(){
		$galleries=\App\Gallery::where('state',200)->orderBy('count_read','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/gallery').'">갤러리 방문 수</a></h4><ul>';
		foreach($galleries as $gallery){
			$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
			$content.='<li><a href="'.url('/admin/gallery/'.$gallery->id).'">'.$gallery->name.'&nbsp;<span>'.$gallery->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 갤러리 액자 수
	static public function cardCountcadre(){
		$galleries=\App\Gallery::where('state',200)->orderBy('count_cadre','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/gallery').'">갤러리 액자 수</a></h4><ul>';
		foreach($galleries as $gallery){
			$gallery->name=\App\Encryption::checkEncrypted($gallery->name)?\App\Encryption::decrypt($gallery->name):$gallery->name;
			$content.='<li><a href="'.url('/admin/gallery/'.$gallery->id).'">'.$gallery->name.'&nbsp;<span>'.$gallery->count_cadre.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 액자 조회 수
	static public function cardCountreadCadre(){
		$cadres=\App\Cadre::where('state',200)->orderBy('count_read','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/gallery').'">액자 조회 수</a></h4><ul>';
		foreach($cadres as $cadre){
			$content.='<li><a href="'.url('/'.$cadre->gallery()->url.'/'.$cadre->id).'" target="_blank">';
			foreach($cadre->files() as $file)
				$content.='<img src="/file/thumb/'.$file->name.'" alt="">';
			$content.='&nbsp;<span>'.$cadre->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
}