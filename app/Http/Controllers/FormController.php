<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;
use File;
use Response;

class FormController extends Controller {
	
	static public function routes(){
		\Route::get('/form/{skin}/image/{name}','FormController@getImageResource')->where('name','(.*)');
		\Route::get('/form/{skin}/style/{name}','FormController@getStyleResource')->where('name','(.*)');
		\Route::get('/form/{skin}/font/{name}','FormController@getFontResource')->where('name','(.*)');
		\Route::get('/form/{skin}/script/{name}','FormController@getScriptResource')->where('name','(.*)');
		
		\Route::get('/admin/form','FormController@getAdminList');
		\Route::get('/admin/form/create','FormController@getAdminCreate');
		\Route::post('/admin/form/create','FormController@postAdminCreate');
		\Route::get('/admin/form/{id}','FormController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/form/edit','FormController@postAdminEdit');
		\Route::post('/admin/form/delete','FormController@postAdminDelete');
		
		\Route::get('/admin/form/answer','FormController@getAdminAnswerList');
		\Route::get('/admin/form/answer/{id}','FormController@getAdminAnswerFormList')->where('id','[0-9]+');
		\Route::post('/admin/form/answer/delete','FormController@getAdminAnswerDelete');
	}
	
	static public function admin_menu(){
		if(!FormController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/form',
				'name'=>'폼 관리',
				'external'=>false,
				'current'=>'form',
				'submenu'=>[
					[
						'url'=>'/admin/form/create',
						'name'=>'폼 추가',
						'external'=>false,
						'current'=>'create',
					],
					[
						'url'=>'/admin/form/answer',
						'name'=>'답변 보기',
						'external'=>false,
						'current'=>'answer',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!FormController::checkAuthority(true))
			return null;
			
		return [
			FormController::cardCountread(),
			FormController::cardCountanswer(),
		];
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','form')->first()->manager;
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
		$path=base_path().'/resources/views/form/'.$skin.'/_image/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;

	}
	
	public function getStyleResource($skin,$name){
		$path=base_path().'/resources/views/form/'.$skin.'/_style/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type]);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	public function getFontResource($skin,$name){
		$path=base_path().'/resources/views/form/'.$skin.'/_style/'.$name;
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
		$path=base_path().'/resources/views/form/'.$skin.'/_script/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/javascript';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
    
    // 관리자 폼 > 폼 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		View::share('current',['form',null]);
		
		$query=\App\Form::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('form.admin.list',['forms'=>$query]);
	}
    
    // 관리자 폼 > 폼 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		View::share('current',['form','create']);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/form/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/form/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('form.admin.create',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 폼 > 폼 관리 > 추가
    // [POST] 새 폼 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		
		$id=Controller::getSequence();
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		if(!$request->group_mail) $request->group_mail=[];
		$group_mail=implode('|',$request->group_mail);
		if(!$request->url) $request->url='';
		\App\Form::create([
			'id'=>$id,
			'url'=>$request->url,
			'name'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->name):$request->name,
			'allowed_group'=>$group,
			'allowed_group_mail'=>$group_mail,
			'layout'=>$request->layout,
			'skin'=>$request->skin,
			'count_question'=>count($request->extravar),
			'start_at'=>str_replace('T',' ',$request->start_at).':00',
			'end_at'=>str_replace('T',' ',$request->end_at).':59',
			'state'=>200,
		]);
		
		$form=\App\Form::find($id);
		
		for($i=0;$i<count($request->extravar)-1;$i++){
			DB::table('form_questions')->insert([
				'id'=>Controller::getSequence(),
				'form'=>$id,
				'name'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
				'type'=>$request->extravar_type[$i],
				'order_show'=>$i,
				'content'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
				'state'=>200,
				'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
				'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			]);
		}
        
        DB::table('ids')->insert([
	        'id'=>$request->url,
	        'module'=>'form',
        ]);
		
		Controller::notify('<u>'.$request->name.'</u> 폼을 추가했습니다.');
		return redirect('/admin/form/'.$form->id)->with(['message'=>'폼을 추가했습니다.']);
	}
    
    // 관리자 폼 > 폼 관리 > 폼(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		View::share('current',['form',null]);
		
		$form=\App\Form::where(['id'=>$id,'state'=>200])->first();
		if(!$form) abort(404);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/form/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/form/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('form.admin.create',['form'=>$form,'layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 폼 > 폼 관리 > 폼(보기)
    // [POST] 폼 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		
		$form=\App\Form::where(['id'=>$request->id,'state'=>200])->first();
		if(!$form) abort(404);
        
        if($form->url!=$request->url){
	        DB::table('ids')->where([
		        'id'=>$form->url,
		        'module'=>'form',
	        ])->delete();
	        DB::table('ids')->insert([
		        'id'=>$request->url,
		        'module'=>'form',
	        ]);
	    }
	    
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		$form->allowed_group=$group;
		if(!$request->group_mail) $request->group_mail=[];
		$group_mail=implode('|',$request->group_mail);
		$form->allowed_group_mail=$group_mail;
		if(!$request->url) $request->url='';
		
		$form->name=\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name;
		
		Controller::notify(($form->name!=$request->name?'<u>'.$form->name.'</u> → ':'').'<u>'.$request->name.'</u> 폼을 수정했습니다.');
		
		$form->url=$request->url;
		$form->name=\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->name):$request->name;
		$form->layout=$request->layout;
		$form->skin=$request->skin;
		$form->count_question=count($request->extravar);
		$form->start_at=str_replace('T',' ',$request->start_at).':00';
		$form->end_at=str_replace('T',' ',$request->end_at).':59';
		
		$form->save();
		
		DB::table('form_questions')->where(['form'=>$form->id,'state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->extravar)-1;$i++){
			if(!$request->extravar[$i]){
				DB::table('form_questions')->insert([
					'id'=>Controller::getSequence(),
					'form'=>$form->id,
					'name'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],

					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],

					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('form_questions')->where(['form'=>$form->id,'id'=>$request->extravar[$i]])->update([
					'name'=>$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>$request->extravar_content[$i],
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
		
		return redirect('/admin/form/'.$form->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'폼을 수정했습니다.']);
	}
    
    // 관리자 폼 > 폼 관리 > 수정(보기)
    // [POST] 폼 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		
		$form=\App\Form::where(['id'=>$request->id,'state'=>200])->first();
		if(!$form) abort(404);
		
        DB::table('ids')->where([
	        'id'=>$form->url,
	        'module'=>'form',
        ])->delete();
        
        DB::table('form_questions')->where(['form'=>$form->id,'state'=>200])->update(['state'=>410]);
        DB::table('form_answers')->where(['form'=>$form->id,'state'=>200])->update(['state'=>410]);
        DB::table('form_answer_items')->where(['form'=>$form->id,'state'=>200])->update(['state'=>410]);
        // 41x은 상위 오브젝트가 사라지면서 삭제된 경우
		
		$form->state=400;
		$form->save();
		
		$form->name=\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name;
		
		Controller::notify('<u>'.$form->name.'</u> 폼을 삭제했습니다.');
		return redirect('/admin/form'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','폼을 삭제했습니다.');
	}
	
	// 관리자 폼 > 답변 목록 (폼 목록)
	public function getAdminAnswerList(){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		View::share('current',['form','answer']);
		
		$query=\App\Form::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('form.admin.list_form',['forms'=>$query]);
	}
	
	// 관리자 폼 > 답변 목록
	public function getAdminAnswerFormList($id){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		View::share('current',['form','answer']);
		
		$form=\App\Form::where(['id'=>$id,'state'=>200])->first();
		if(!$form) abort(404);
		
		return view('form.admin.list_answer',['form'=>$form]);
	}
	
	// 관리자 폼 > 답변 읽기
    // [POST] 답변 삭제
	public function getAdminAnswerDelete(Request $request){
		Controller::logActivity('USR');
		FormController::checkAuthority();
		
		foreach($request->answers as $id){
			$answer=\App\Answer::where(['id'=>$id,'state'=>200])->first();
			$answer->timestamps=false;
			$answer->state=401;
			$answer->save();
			DB::table('form_answer_items')->where('answer',$id)->update([
				'state'=>401,
			]);
			$form=$answer->form();
			$form->timestamps=false;
			$form->decrement('count_answer');
		}
        // 401은 관리자에 의해 삭제된 경우
		
		Controller::notify('답변을 일괄 삭제했습니다.');
		return redirect('/admin/form/answer/'.$form->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','답변을 삭제했습니다.');
	}
	
	// 답변 쓰기
	public function getList($url){
		return $this->getCreate($url);
	}
	
	public function getCreate($url){
		Controller::logActivity('USR');
		
		$form=\App\Form::where(['url'=>$url,'state'=>200])->first();
		if(!$form) abort(404);
		if(!$form->authority()) abort(401);
		
		$form->timestamps=false;
		$form->increment('count_read');
		
		return view('form.'.$form->skin.'.create',['layout'=>$form->layout?\App\Layout::find($form->layout):null,'form'=>$form]);
	}
	
	// 답변 쓰기
	// [POST] 답변 쓰기
	public function postCreate(Request $request,$url){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$form=\App\Form::where(['url'=>$url,'state'=>200])->first();
		if(!$form) abort(404);
		if(!$form->inPeriod()) abort(404);
		if(!$form->authority()) abort(401);
		
		$id=Controller::getSequence();
		
		\App\Answer::create([
			'id'=>$id,
			'form'=>$form->id,
			'author'=>Auth::check()?Auth::user()->id:null,
			'state'=>200,
			'ip_address'=>$request->ip(),
		]);
		
		if(count($form->questions())){
			foreach($form->questions() as $extravar){
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
				
				DB::table('form_answer_items')->insert([
					'answer'=>$id,
					'question'=>$extravar->id,
					'form'=>$form->id,
					'content'=>\App\Encryption::isEncrypt('form')?\App\Encryption::encrypt($content):$content,
					'state'=>200,
				]);
			}
		}
		
		$form->timestamps=false;
		$form->increment('count_answer');
		
		$redirect='/'.$form->url.'/complete';
		
		
		// 메일 발송
		$answer=\App\Answer::find($id);
		$mail_content='';
		foreach($form->questions() as $question){
			$mail_content.='<div class="question">'.(\App\Encryption::checkEncrypted($question->name)?\App\Encryption::decrypt($question->name):$question->name).'</div><p>';
			if($question->type=='text'){
				if($answer->item($question->id))
					 $mail_content.=htmlspecialchars($answer->item($question->id));
			}elseif($question->type=='textarea'){
				if($answer->item($question->id))
					$mail_content.=str_replace('&lt;br /&gt;','<br>',htmlspecialchars(nl2br($answer->item($question->id))));
			}elseif($question->type=='radio'){
				if($answer->item($question->id))
					$mail_content.=htmlspecialchars($answer->item($question->id));
			}elseif($question->type=='checkbox'){
				if(count($answer->item($question->id)))
					$mail_content.=htmlspecialchars(implode(', ',$answer->item($question->id)));
			}elseif($question->type=='order'){
				if(count($answer->item($question->id)))
					$mail_content.=htmlspecialchars(implode(', ',$answer->item($question->id)));
			}elseif($question->type=='image'){
				if($answer->item($question->id))
				$mail_content.='<img src="'.htmlspecialchars(url($answer->item($question->id))).'" alt="">';
			}elseif($question->type=='file'){
				if($answer->item($question->id))
					$mail_content.='.💾 '.htmlspecialchars(\App\File::where('name',str_replace('/file/','',$answer->item($question->id)))->first()->original);
			}
			$mail_content.='&nbsp;</p>';
		}
		
		$form->name=\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name;
		
		foreach($form->mailing_list() as $email){
			AdminController::sendmail($email,'['.\App\Setting::find('app_name')->content.'] '.$form->name.'에 새로운 답변','<a href="'.url($form->url).'">'.$form->name.'</a> 폼에 답변이 새로 작성되었습니다. '.($mail_content?'<div class="content">'.$mail_content.'</div>':''));
		}
		
		Controller::notify('<u>'.$form->name.'</u> 폼에 답변을 작성했습니다.');
		return redirect($redirect.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
	
	// 답변 쓰기 - 게시글 열람 권한 없을 때 오는 작성 완료 페이지
	public function getComplete($url){
		Controller::logActivity('USR');
		
		$form=\App\Form::where(['url'=>$url,'state'=>200])->first();
		if(!$form) abort(404);
		if(!$form->inPeriod()) abort(404);
		
		return view('form.'.$form->skin.'.complete',['layout'=>$form->layout?\App\Layout::find($form->layout):null,'form'=>$form]);
	}	
	
	// 카드 - 방문 수
	static public function cardCountread(){
		$forms=\App\Form::where('state',200)->orderBy('count_read','desc')->limit(5)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/form').'">폼 방문 수</a></h4><ul>';
		foreach($forms as $form){
			$form->name=\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name;
			$content.='<li><a href="'.url('/admin/form/'.$form->id).'">'.$form->name.'&nbsp;<span>'.$form->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	
	// 카드 - 답변 수
	static public function cardCountanswer(){
		$forms=\App\Form::where('state',200)->orderBy('count_answer','desc')->limit(5)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/form').'">폼 답변 수</a></h4><ul>';
		foreach($forms as $form){
			$form->name=\App\Encryption::checkEncrypted($form->name)?\App\Encryption::decrypt($form->name):$form->name;
			$content.='<li><a href="'.url('/admin/form/answer/'.$form->id).'">'.$form->name.'&nbsp;<span>'.$form->count_answer.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
}