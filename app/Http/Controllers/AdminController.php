<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;

class AdminController extends Controller {
	   
    public function __construct(){
      $this->middleware('auth', ['except'=>['getDashBoard']]);
    }
	
	static public function routes(){
		\Route::get('/admin', 'AdminController@getDashBoard');
		\Route::get('/admin/setting', 'AdminController@getSetting');
		\Route::post('/admin/setting', 'AdminController@postSetting');
		\Route::get('/admin/manager', 'AdminController@getManager');
		\Route::post('/admin/manager', 'AdminController@postManager');
		\Route::get('/admin/visitor', 'AdminController@getVisitor');
		\Route::get('/admin/notification', 'AdminController@getNotification');
		
		\Route::get('/admin/check/url', 'AdminController@getCheckDuplicate');
	}
    
    static public function checkAuthority($boolean=false){
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
	    if(array_key_exists(2,Auth::user()->groups()))
	    // 관리자 페이지는 2번 그룹('관리자')이면 누구나 접근 가능, 모듈 별 설정은 그쪽 컨트롤러에서...
	    	return true;
	    if(!$boolean) abort(401);
    	return false;
    }
    
    static public function menu(){
	    $menus=[];
	    $modules=DB::table('modules')->orderBy('order_group')->orderBy('order_show')->get();
	    foreach($modules as $module){
		    $menu=('\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller')::admin_menu();
		    if($menu)
			    $menus[$module->order_group][]=$menu;
	    }
	    return $menus;
    }
    
    public static function sendmail($address,$subject,$content){
		if($address!=\App\User::find(4)->email&&\App\Setting::find('mail_host')->content&&\App\Setting::find('mail_port')->content&&\App\Setting::find('mail_username')->content&&\App\Setting::find('mail_password')->content){
			config(['mail.host'=>\App\Setting::find('mail_host')->content]);
			config(['mail.port'=>\App\Setting::find('mail_port')->content]);
			config(['mail.username'=>\App\Setting::find('mail_username')->content]);
			config(['mail.password'=>\App\Setting::find('mail_password')->content]);
			config(['mail.encryption'=>\App\Setting::find('mail_encryption')->content]);
			
			\Mail::send('mail.'.\App\Setting::find('mail_template')->content,['data'=>$content],function($message) use ($address,$subject,$content){
				$message->from(\App\Setting::find('mail_address')->content,\App\Setting::find('app_name')->content)->to($address)->subject($subject);
				$message->getSwiftMessage();
			});
		}
	}
    
    // 대시보드
	public function getDashBoard(){
		Controller::logActivity('USR');
		if(Auth::check()){
			AdminController::checkAuthority();
			View::share('current',['dashboard',null]);
			
		    $cards=[
			    0=>[[
					AdminController::cardVisitor(),
					AdminController::cardNotification(),
					AdminController::cardVersion(),
			    ]],
		    ];
		    $modules=DB::table('modules')->orderBy('order_group')->orderBy('order_show')->get();
		    foreach($modules as $module){
			    $card=('\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller')::admin_card();
			    if($card)
				    $cards[$module->order_group][]=$card;
		    }
		    
		//	AdminController::sendmail('manapie@me.com','제목','내용');
			
			return view('admin.dashboard',['cards'=>$cards]);
		}else{
			return view('admin.login');
		}
	}
    
    // 사이트 기본 설정
	public function getSetting(){
	    if(array_key_exists(1,Auth::user()->groups())){
			Controller::logActivity('USR');
			View::share('current',['setting',null]);
		
			$paths=[];
			foreach(glob(base_path().'/resources/views/mail/*') as $path){
				$paths[]=$path=str_replace('.blade.php','',str_replace(base_path().'/resources/views/mail/','',$path));
			}
			
			return view('admin.setting',['mail_templates'=>$paths]);
		}else{
			abort(401);
		}
	}
    
    // 사이트 기본 설정
    // [POST] 기본 설정 저장
	public function postSetting(Request $request){
	    if(array_key_exists(1,Auth::user()->groups())){
			Controller::logActivity('USR');
			View::share('current',['setting',null]);
			
			$settings=['app_name','app_description','app_preview','mail_address','mail_host','mail_port','mail_username','mail_password','mail_encryption','mail_template'];
			
			foreach($settings as $set){
				$setting=\App\Setting::find($set);
				if($set=='app_preview'){
					$file=$request->file('app_preview');
					if($request->hasFile('app_preview'))
						$request->app_preview=ResourceController::saveFile('image',$file,1);
					else
						$request->app_preview=$request->app_preview_original??null;
				}
				
				if($setting->content!=$request->$set){
					$setting->content=$request->$set;
					$setting->author=Auth::user()->id;
					$setting->save();
				}
			}
			
			Controller::notify('사이트 기본 설정을 저장했습니다.');
			return redirect()->back()->with('message','설정을 저장했습니다.');
		}else{
			abort(401);
		}
	}
    
    // 관리 권한 설정
	public function getManager(){
	    if(array_key_exists(1,Auth::user()->groups())){
			Controller::logActivity('USR');
			View::share('current',['manager',null]);
			
			return view('admin.manager',['modules'=>DB::table('modules')->orderBy('order_group')->orderBy('order_show')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get()]);
		}else{
			abort(401);
		}
	}
    
    // 관리 권한 설정
    // [POST] 관리자 역할 저장
	public function postManager(Request $request){
	    if(array_key_exists(1,Auth::user()->groups())){
			Controller::logActivity('USR');
			View::share('current',['manager',null]);
			
			foreach($request->module as $module){
				$former=DB::table('modules')->where('module',$module)->first()->manager;
				if(!$request->$module) $request->$module=[];
				$new=implode('|',$request->$module);
				if($new!=$former)
					DB::table('modules')->where('module',$module)->update(['manager'=>$new,'updated_at'=>DB::raw('CURRENT_TIMESTAMP')]);
			}
			
			Controller::notify('관리 권한을 설정했습니다.');
			return redirect()->back()->with('message','권한을 저장했습니다.');
		}else{
			abort(401);
		}
	}
	
	// URL 중복 검사
	public function getCheckDuplicate(){
		Controller::logActivity('USR');
		AdminController::checkAuthority();
		
		if(!$_GET['url']) $_GET['url']='';
		if(!isset($_GET['original'])||!$_GET['original']) $_GET['original']='';
		if(!$_GET['url']||$_GET['url']!=$_GET['original'])
			if(DB::table('ids')->where('id',$_GET['url'])->first())
				echo 'Y';
	}
	
    
    // 접속 로그
	public function getVisitor(){
		Controller::logActivity('USR');
		View::share('current',['visitor',null]);
		AdminController::checkAuthority();
		$logs=DB::table('log_activity');
		
		if(!isset($_GET['from'])||!$_GET['from']) $_GET['from']=date('Y-m-d');
		if(!isset($_GET['to'])||!$_GET['to']) $_GET['to']=date('Y-m-d');
		
		$logs=DB::table('log_activity')->whereBetween('created_at',[$_GET['from'].' 00:00:00',$_GET['to'].' 23:59:59']);
		
		$logs=$logs->where(function($q){
			$q->where('referer','not like','%'.str_replace('www.','',parse_url(url(''),PHP_URL_HOST)).'%')->orWhereNull('referer');
		})->orderBy('id','desc');
		
		return view('admin.visitor',['count'=>$logs->count(),'logs'=>$logs->paginate(30)]);
	}
	
    
    // 알림
	public function getNotification(){
		Controller::logActivity('USR');
		View::share('current',['notification',null]);
		AdminController::checkAuthority();
		
		$notifications=DB::table('notifications');
		
		if(isset($_GET['from'])||isset($_GET['to'])){
			if(!isset($_GET['from'])||!$_GET['from']) $_GET['from']=date('Y-m-d');
			if(!isset($_GET['to'])||!$_GET['to']) $_GET['to']=date('Y-m-d');
			
			$notifications=$notifications->where(function($q){
				$q->whereBetween('created_at',[$_GET['from'].' 00:00:00',$_GET['to'].' 23:59:59']);
			});
		}
		$notifications=$notifications->orderBy('id','desc')->paginate(30);
		
		return view('admin.notification',['notifications'=>$notifications]);
	}
	
	// 카드 - 방문자 수
	static public function cardVisitor(){
		$logs=DB::table('log_activity')->whereBetween('created_at',[date('Y-m-d').' 00:00:00',date('Y-m-d').' 23:59:59']);
		
		$logs=$logs->where(function($q){
			$q->where('referer','not like','%'.str_replace('www.','',parse_url(url(''),PHP_URL_HOST)).'%')->orWhereNull('referer');
		});
		
		$content='<a href="'.url('/admin/visitor').'"><div class="card_visitors">'.date('Y-m-d').' ~<br>'.date('Y-m-d').' 기간 중 방문자 수 <h5>'.$logs->count().'</h5></div></a>';
		
		return $content;
	}
	
	// 카드 - 알림
	static public function cardNotification(){
	//	$notifications=DB::table('notifications')->where('user',Auth::user()->id)->orderBy('id','desc')->limit(15)->get();
		$notifications=DB::table('notifications')->orderBy('id','desc')->limit(15)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/notification').'">알림</a></h4><ul>';
		foreach($notifications as $noti){
			$content.='<li>'.$noti->message.($noti->author?' <div class="user">'.\App\User::find($noti->author)->nickname.', '.$noti->created_at.'</div>':'').'</li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 버전
	static public function cardVersion(){
		$content='<a href="'.url('http://cms.manapie.me/').'"><div class="card_visitors" style="text-align:right">MANAPIE CMS '.Controller::getVersion().'</div></a>';
		
		$content.='<div class="card_list"><ul>';
			$content.='<li><a href="http://cms.manapie.me/guide" target="_blank">공통 가이드</a></li>';
		$content.='</ul></div>';
		
		return $content;
	}
	
}