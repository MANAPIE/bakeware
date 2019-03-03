<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class UserController extends Controller {
	
	static public function routes(){
		\Route::get('/admin/user','UserController@getAdminList');
		\Route::get('/admin/user/create','UserController@getAdminCreate');
		\Route::post('/admin/user/create','UserController@postAdminCreate');
		\Route::get('/admin/user/{id}','UserController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/user/edit','UserController@postAdminEdit');
		\Route::post('/admin/user/delete','UserController@postAdminDelete');
		
		\Route::get('/admin/user/group','UserController@getAdminGroup');
		\Route::post('/admin/user/group','UserController@postAdminGroup');
		
		\Route::get('/admin/user/setting','UserController@getAdminSetting');
		\Route::post('/admin/user/setting','UserController@postAdminSetting');
		
		\Route::get('/user/check','UserController@getCheckDuplicate');
		\Route::get('/user/profile/{id}','UserController@getProfile');
	}
	
	static public function admin_menu(){
		if(!UserController::checkAuthority(true))
			return null;
			
		return [
			[
				'url'=>'/admin/user',
				'name'=>'회원 관리',
				'external'=>false,
				'current'=>'user',
				'submenu'=>[
					[
						'url'=>'/admin/user/create',
						'name'=>'회원 추가',
						'external'=>false,
						'current'=>'create',
					],
					[
						'url'=>'/admin/user/group',
						'name'=>'역할 관리',
						'external'=>false,
						'current'=>'group',
					],
					[
						'url'=>'/admin/user/setting',
						'name'=>'회원 설정',
						'external'=>false,
						'current'=>'setting',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!UserController::checkAuthority(true))
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
		
		$manager=DB::table('modules')->where('module','user')->first()->manager;
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
    
    // 관리자 페이지 > 회원 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		View::share('current',['user',null]);
		
		$query=\App\User::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where(function($q){
				$q->where('name','like','%'.$_GET['keyword'].'%')->orWhere('nickname','like','%'.$_GET['keyword'].'%');
			});
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('user.admin.list',['users'=>$query]);
	}
    
    // 관리자 페이지 > 회원 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		View::share('current',['user','create']);
		
		return view('user.admin.create',['groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
	}
    
    // 관리자 페이지 > 회원 관리 > 추가
    // [POST] 새 회원 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		
		if($request->password!=$request->password_confirm)
			return redirect()->back()->withInput()->with(['message'=>'비밀번호 확인이 일치하지 않습니다.']);
        
        $user=Controller::getSequence();
        \App\User::create([
	        'id'=>$user,
	        'name'=>\App\Encryption::isEncrypt('user')?\App\Encryption::rt_encrypt($request->name):$request->name,
	        'nickname'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->nickname):$request->nickname,
	        'password'=>\Hash::make($request->password),
			'state'=>200,
	        'email'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->email):$request->email,
	        'note'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->note):$request->note,
        ]);

		if($request->hasFile('profile')){ // 프로필 사진
			$file=$request->file('profile');
		    $this->makeThumbnail($file);
		    ResourceController::saveFile('image',$file,$user);
		}
		
        if($request->group)
	        foreach($request->group as $group)
		        DB::table('users_groups')->insert([
			        'user'=>$user,
			        'group'=>$group,
		        ]);
		
		if(count(\App\User::extravars())){
			foreach(\App\User::extravars() as $extravar){
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
				
				DB::table('user_extravars')->insert([
					'extravar'=>$extravar->id,
					'user'=>$user,
					'content'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}

		Controller::notify('<u>'.$request->nickname.'</u> 회원을 추가했습니다.',$user);
		return redirect('/admin/user/'.$user)->with(['message'=>'회원를 추가했습니다.']);
	}
    
    // 관리자 페이지 > 회원 관리 > 수정(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		View::share('current',['user',null]);
		
		$user=\App\User::where(['id'=>$id,'state'=>200])->first();
		if(!$user) abort(404);
		
		return view('user.admin.create',['user'=>$user,'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
	}
    
    // 관리자 페이지 > 회원 관리 > 수정(보기)
    // [POST] 회원 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		
		$user=\App\User::where(['id'=>$request->id,'state'=>200])->first();
		if(!$user) abort(404);
		
		if($request->password){
			if($request->password!=$request->password_confirm)
				return redirect()->back()->withInput()->with(['message'=>'비밀번호 확인이 일치하지 않습니다.']);
				
			$user->password=\Hash::make($request->password);
		}
		
		$user->nickname=\App\Encryption::checkEncrypted($user->nickname)?\App\Encryption::decrypt($user->nickname):$user->nickname;
		
		Controller::notify(($user->nickname!=$request->nickname?'<u>'.$user->nickname.'</u> → ':'').'<u>'.$request->nickname.'</u> 회원 정보를 수정했습니다.',$user->id);
		
		$user->name=\App\Encryption::isEncrypt('user')?\App\Encryption::rt_encrypt($request->name):$request->name;
		$user->nickname=\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->nickname):$request->nickname;
		$user->email=\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->email):$request->email;
		$user->note=\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->note):$request->note;
		$user->save();

		if($request->hasFile('profile')){ // 프로필 사진
			$file=$request->file('profile');
		    $this->makeThumbnail($file);
		    ResourceController::saveFile('image',$file,$user->id);
		}
		
		DB::table('users_groups')->where('user',$request->id)->delete();
        if($request->group)
	        foreach($request->group as $group)
		        DB::table('users_groups')->insert([
			        'user'=>$request->id,
			        'group'=>$group,
		        ]);
		        
		DB::table('user_extravars')->where('user',$user->id)->delete();
		if(count(\App\User::extravars())){
			foreach(\App\User::extravars() as $extravar){
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
				
				DB::table('user_extravars')->insert([
					'extravar'=>$extravar->id,
					'user'=>$user->id,
					'content'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}
		
		return redirect('/admin/user/'.$user->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'회원 정보를 수정했습니다.']);
	}
	
	protected function makeThumbnail($file){
		$imgSrc=$file->getRealPath();
		list($width,$height)=getimagesize($imgSrc);
		if($file->getClientMimeType()=='image/gif')
	    	$myImage=imagecreatefromgif($imgSrc);
		elseif($file->getClientMimeType()=='image/png')
	  		$myImage=imagecreatefrompng($imgSrc);
	    else
	    	$myImage=imagecreatefromjpeg($imgSrc);
	    	
	    if($width>$height){
	        $y=0;
	        $x=($width-$height)/2;
	        $smallestSide=$height;
	    }else{
	        $x=0;
	        $y=($height-$width)/2;
	        $smallestSide=$width;
	    }
	    $thumb=imagecreatetruecolor(300,300); // 300x300 cover로 리사이즈
	    imagecopyresampled($thumb,$myImage,-1,-1,$x,$y,302,302,$smallestSide,$smallestSide);
	    imagejpeg($thumb,$imgSrc); // 원본에 덮어씌움
	    
	    @imagedestroy($myImage);
	    @imagedestroy($thumb);
	}
    
    // 관리자 페이지 > 회원 관리 > 수정(보기)
    // [POST] 회원 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		
		$user=\App\User::where(['id'=>$request->id,'state'=>200])->first();
		if(!$user) abort(404);
		
		$user->state=400;
		$user->save();
		
		$user->nickname=\App\Encryption::checkEncrypted($user->nickname)?\App\Encryption::decrypt($user->nickname):$user->nickname;
		
		Controller::notify('<u>'.$user->nickname.'</u> 회원을 삭제했습니다.',$user->id);
		return redirect('/admin/user'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','회원을 삭제했습니다.');
	}
    
    // 관리자 페이지 > 회원 관리 > 역할 관리
	public function getAdminGroup(){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		View::share('current',['user','group']);
		
		return view('user.admin.group',['groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
	}
    
    // 관리자 페이지 > 회원 관리 > 역할 관리
    // [POST] 역할 저장
	public function postAdminGroup(Request $request){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		
		for($i=0;$i<count($request->id)-1;$i++){
			if($request->id[$i]>2){
				$query=DB::table('users_group')->where(['id'=>$request->id[$i],'state'=>200])->first();
				$query->name=\App\Encryption::checkEncrypted($query->name)?\App\Encryption::decrypt($query->name):$query->name;
		
				if($query->name!=$request->name[$i])
					DB::table('users_group')->where(['id'=>$request->id[$i],'state'=>200])->update(['name'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->name[$i]):$request->name[$i],'updated_at'=>DB::raw('CURRENT_TIMESTAMP')]);
			}
			else{
		        DB::table('users_group')->insert([
			        'id'=>Controller::getSequence(),
			        'name'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->name[$i]):$request->name[$i],
					'state'=>200,
			        'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			        'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
		        ]);
			}
		}
		if($request->deleted)
			foreach(explode('|',$request->deleted) as $group){
				if($group>3)
					DB::table('users_group')->where(['id'=>$group,'state'=>200])->update(['state'=>400,'updated_at'=>DB::raw('CURRENT_TIMESTAMP')]);
			}
			
		Controller::notify('회원 역할을 갱신했습니다.');
		return redirect()->back()->with(['message'=>'역할을 저장했습니다.']);
	}
    
    // 관리자 페이지 > 회원 관리 > 회원 설정
	public function getAdminSetting(){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		View::share('current',['user','setting']);
		
		return view('user.admin.setting');
	}
    
    // 관리자 페이지 > 회원 관리 > 회원 설정
    // [POST] 역할 저장
	public function postAdminSetting(Request $request){
		Controller::logActivity('USR');
		UserController::checkAuthority();
		
		DB::table('user_extravar')->where(['state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->extravar)-1;$i++){
			if(!$request->extravar[$i]){
				DB::table('user_extravar')->insert([
					'id'=>Controller::getSequence(),
					'name'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'description'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_description[$i]):$request->extravar_description[$i],
					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('user_extravar')->where(['id'=>$request->extravar[$i]])->update([
					'name'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'description'=>\App\Encryption::isEncrypt('user')?\App\Encryption::encrypt($request->extravar_description[$i]):$request->extravar_description[$i],
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
			
		Controller::notify('회원 설정을 갱신했습니다.');
		return redirect()->back()->with(['message'=>'설정을 저장했습니다.']);
	}
    
    // 아이디 중복 검사
	public function getCheckDuplicate(){
		Controller::logActivity('USR');
		
		if(\App\User::where(['name'=>$_GET['name'],'state'=>200])->first() || \App\User::where(['name'=>\App\Encryption::rt_encrypt($_GET['name']),'state'=>200])->first())
			echo 'Y';
	}
	
	// 프로필 사진 가져오기
	public function getProfile($id){
		Controller::logActivity('USR');
		
		$user=\App\User::where(['id'=>$id,'state'=>200])->first();
		if(!$user) abort(404);
		
		$data=DB::table('files')->where(['article'=>$id,'type'=>'image'])->orderBy('id','desc')->first();
		if($data==null) abort(404);
		
		$file=\Storage::get($data->name);
		DB::table('files')->where('id',$data->id)->increment('count_download');
		return response($file,200)->withHeaders(['Content-Type'=>$data->mime,'Cache-Control'=>'public,max-age=86400']);
	}
	
}