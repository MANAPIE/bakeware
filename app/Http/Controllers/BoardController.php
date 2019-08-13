<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;
use File;
use Response;

class BoardController extends Controller {
	
	static public function routes(){
		\Route::get('/board/{skin}/image/{name}','BoardController@getImageResource')->where('name','(.*)');
		\Route::get('/board/{skin}/style/{name}','BoardController@getStyleResource')->where('name','(.*)');
		\Route::get('/board/{skin}/font/{name}','BoardController@getFontResource')->where('name','(.*)');
		\Route::get('/board/{skin}/script/{name}','BoardController@getScriptResource')->where('name','(.*)');
		
		\Route::get('/admin/board','BoardController@getAdminList');
		\Route::get('/admin/board/create','BoardController@getAdminCreate');
		\Route::post('/admin/board/create','BoardController@postAdminCreate');
		\Route::get('/admin/board/{id}','BoardController@getAdminEdit')->where('id','[0-9]+');
		\Route::post('/admin/board/edit','BoardController@postAdminEdit');
		\Route::post('/admin/board/delete','BoardController@postAdminDelete');
		
		\Route::get('/admin/board/{id}/documents','BoardController@getAdminDocumentsList')->where('id','[0-9]+');
		\Route::get('/admin/board/{id}/documents/create','BoardController@getAdminDocumentsCreate')->where('id','[0-9]+');
		\Route::post('/admin/board/documents/create','BoardController@postAdminDocumentsCreate');
		\Route::get('/admin/board/{id}/documents/{document}','BoardController@getAdminDocumentsEdit')->where('id','[0-9]+')->where('document','[0-9]+');
		\Route::post('/admin/board/documents/edit','BoardController@postAdminDocumentsEdit');
		\Route::post('/admin/board/documents/delete','BoardController@postAdminDocumentsDelete');
		
		
		\Route::get('/admin/board/document','BoardController@getAdminDocumentList');
		\Route::post('/admin/board/document/delete','BoardController@postAdminDocumentDelete');
		
		\Route::get('/admin/board/comment','BoardController@getAdminCommentList');
		\Route::post('/admin/board/comment/delete','BoardController@postAdminCommentDelete');
		
		\Route::get('/board/comment/{id}','BoardController@getCommentEdit')->where('id','[0-9]+');
		\Route::post('/board/comment/{id}','BoardController@postCommentEdit')->where('id','[0-9]+');
		\Route::post('/board/comment/delete/{id}','BoardController@postCommentDelete')->where('id','[0-9]+');
	}
	
	static public function admin_menu(){
		if(!BoardController::checkAuthority(true))
			return null;
		
		return [
			[
				'url'=>'/admin/board',
				'name'=>'게시판 관리',
				'external'=>false,
				'current'=>'board',
				'submenu'=>[
					[
						'url'=>'/admin/board',
						'name'=>'게시판 관리',
						'external'=>false,
						'current'=>null,
					],
					[
						'url'=>'/admin/board/create',
						'name'=>'게시판 추가',
						'external'=>false,
						'current'=>'create',
					],
					[
						'url'=>'/admin/board/document',
						'name'=>'게시글 관리',
						'external'=>false,
						'current'=>'document',
					],
					[
						'url'=>'/admin/board/comment',
						'name'=>'댓글 관리',
						'external'=>false,
						'current'=>'comment',
					],
				],
			],
		];
	}
	
	static public function admin_card(){
		if(!BoardController::checkAuthority(true))
			return null;
			
		return [
			BoardController::cardCountread(),
			BoardController::cardCountdocument(),
			BoardController::cardCountreadDocument(),
			BoardController::cardCountcommentDocument(),
		];
	}
    
    static public function checkAuthority($boolean=false){ // 관리 권한
	    if(!Auth::check()){ // 비로그인은 절대 권한 없음
		    if(!$boolean) abort(401);
	    	return false;
		}
		if(array_key_exists(1,Auth::user()->groups())) // 마스터는 무조건 권한 있음
			return true;
		
		$manager=DB::table('modules')->where('module','board')->first()->manager;
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
		$path=base_path().'/resources/views/board/'.$skin.'/_image/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;

	}
	
	public function getStyleResource($skin,$name){
		$path=base_path().'/resources/views/board/'.$skin.'/_style/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type]);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	public function getFontResource($skin,$name){
		$path=base_path().'/resources/views/board/'.$skin.'/_style/'.$name;
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
		$path=base_path().'/resources/views/board/'.$skin.'/_script/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/javascript';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
    
    // 관리자 게시판 > 게시판 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board',null]);
		
		$query=\App\Board::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('board.admin.list',['boards'=>$query]);
	}
    
    // 관리자 게시판 > 게시판 관리 > 추가
	public function getAdminCreate(){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board','create']);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/board/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/board/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('board.admin.create',['layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 게시판 > 게시판 관리 > 추가
    // [POST] 새 게시판 추가하기
	public function postAdminCreate(Request $request){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		$id=Controller::getSequence();
		
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		if(!$request->group_read) $request->group_read=[];
		$group_read=implode('|',$request->group_read);
		if(!$request->group_document) $request->group_document=[];
		$group_document=implode('|',$request->group_document);
		if(!$request->group_comment) $request->group_comment=[];
		$group_comment=implode('|',$request->group_comment);
		if(!$request->group_mail) $request->group_mail=[];
		$group_mail=implode('|',$request->group_mail);
		if(!$request->url) $request->url='';
		\App\Board::create([
			'id'=>$id,
			'url'=>$request->url,
			'domain'=>$request->domain,
			'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->name):$request->name,
			'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content,
			'sort_by'=>$request->sort_by,
			'sort_order'=>$request->sort_order,
			'allowed_group'=>$group,
			'allowed_group_read'=>$group_read,
			'allowed_group_document'=>$group_document,
			'allowed_group_comment'=>$group_comment,
			'allowed_group_mail'=>$group_mail,
			'anonymous'=>$request->anonymous,
			'layout'=>$request->layout,
			'skin'=>$request->skin,
			'state'=>200,
		]);
		
		$board=\App\Board::find($id);
		
		for($i=0;$i<count($request->category)-1;$i++){
			DB::table('board_categories')->insert([
				'id'=>Controller::getSequence(),
				'board'=>$id,
				'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
				'order_show'=>$i,
				'state'=>200,
				'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
				'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			]);
		}
		
		for($i=0;$i<count($request->extravar)-1;$i++){
			DB::table('board_extravars')->insert([
				'id'=>Controller::getSequence(),
				'board'=>$id,
				'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
				'type'=>$request->extravar_type[$i],
				'order_show'=>$i,
				'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
				'state'=>200,
				'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
				'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
			]);
		}
        
        DB::table('ids')->insert([
	        'id'=>$request->domain.'/'.$request->url,
	        'module'=>'board',
        ]);
		
		Controller::notify('<u>'.$request->name.'</u> 게시판을 추가했습니다.');
		return redirect('/admin/board/'.$board->id)->with(['message'=>'게시판을 추가했습니다.']);
	}
    
    // 관리자 게시판 > 게시판 관리 > 게시판(보기)
	public function getAdminEdit($id){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board',null]);
		
		$board=\App\Board::where(['id'=>$id,'state'=>200])->first();
		if(!$board) abort(404);
		
		$paths=[];
		foreach(glob(base_path().'/resources/views/board/*',GLOB_ONLYDIR) as $path){
			$path=str_replace(base_path().'/resources/views/board/','',$path);
			if($path!='admin')
				$paths[]=$path;
		}
		
		return view('board.admin.create',['board'=>$board,'layouts'=>\App\Layout::where('state',200)->orderBy('id','desc')->get(),'groups'=>DB::table('users_group')->where('state','200')->orderBy('id')->get(),'paths'=>$paths]);
	}
    
    // 관리자 게시판 > 게시판 관리 > 게시판(보기)
    // [POST] 게시판 수정
	public function postAdminEdit(Request $request){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		$board=\App\Board::where(['id'=>$request->id,'state'=>200])->first();
		if(!$board) abort(404);
        
        if($board->url!=$request->url||$board->domain!=$request->domain){
	        DB::table('ids')->where([
		        'id'=>$board->domain.'/'.$board->url,
		        'module'=>'board',
	        ])->delete();
	        DB::table('ids')->insert([
		        'id'=>$request->domain.'/'.$request->url,
		        'module'=>'board',
	        ]);
	    }
	    
		if(!$request->group) $request->group=[];
		$group=implode('|',$request->group);
		$board->allowed_group=$group;
		if(!$request->group_read) $request->group_read=[];
		$group_read=implode('|',$request->group_read);
		$board->allowed_group_read=$group_read;
		if(!$request->group_document) $request->group_document=[];
		$group_document=implode('|',$request->group_document);
		$board->allowed_group_document=$group_document;
		if(!$request->group_comment) $request->group_comment=[];
		$group_comment=implode('|',$request->group_comment);
		$board->allowed_group_comment=$group_comment;
		if(!$request->group_mail) $request->group_mail=[];
		$group_mail=implode('|',$request->group_mail);
		$board->allowed_group_mail=$group_mail;
		if(!$request->url) $request->url='';
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		
		Controller::notify(($board->name!=$request->name?'<u>'.$board->name.'</u> → ':'').'<u>'.$request->name.'</u> 게시판을 수정했습니다.');
		
		$board->url=$request->url;
		$board->domain=$request->domain;
		$board->name=\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->name):$request->name;
		$board->content=\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content;
		$board->sort_by=$request->sort_by;
		$board->sort_order=$request->sort_order;
		$board->anonymous=$request->anonymous;
		$board->layout=$request->layout;
		$board->skin=$request->skin;
		
		$board->save();
		
		DB::table('board_categories')->where(['board'=>$board->id,'state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->category)-1;$i++){
			if(!$request->category[$i]){
				DB::table('board_categories')->insert([
					'id'=>Controller::getSequence(),
					'board'=>$board->id,
					'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
					'order_show'=>$i,
					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('board_categories')->where(['board'=>$board->id,'id'=>$request->category[$i]])->update([
					'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->category_name[$i]):$request->category_name[$i],
					'order_show'=>$i,
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
		
		DB::table('board_extravars')->where(['board'=>$board->id,'state'=>200])->update(['state'=>400]);
		for($i=0;$i<count($request->extravar)-1;$i++){
			if(!$request->extravar[$i]){
				DB::table('board_extravars')->insert([
					'id'=>Controller::getSequence(),
					'board'=>$board->id,
					'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'state'=>200,
					'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}else{
				DB::table('board_extravars')->where(['board'=>$board->id,'id'=>$request->extravar[$i]])->update([
					'name'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_name[$i]):$request->extravar_name[$i],
					'type'=>$request->extravar_type[$i],
					'order_show'=>$i,
					'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->extravar_content[$i]):$request->extravar_content[$i],
					'state'=>200,
					'updated_at'=>DB::raw('CURRENT_TIMESTAMP'),
				]);
				
			}
		}
		
		return redirect('/admin/board/'.$board->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with(['message'=>'게시판을 수정했습니다.']);
	}
    
    // 관리자 게시판 > 게시판 관리 > 수정(보기)
    // [POST] 게시판 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		$board=\App\Board::where(['id'=>$request->id,'state'=>200])->first();
		if(!$board) abort(404);
		
        DB::table('ids')->where([
	        'id'=>$board->domain.'/'.$board->url,
	        'module'=>'board',
        ])->delete();
        
        DB::table('board_documents')->where(['board'=>$board->id,'state'=>200])->update(['state'=>410]);
        DB::table('board_document_comments')->where(['board'=>$board->id,'state'=>200])->update(['state'=>410]);
        // 41x은 상위 오브젝트가 사라지면서 삭제된 경우
		
		$board->state=400;
		$board->save();
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판을 삭제했습니다.');
		return redirect('/admin/board'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','게시판을 삭제했습니다.');
	}
    
    // 관리자 게시판 > 게시판 관리 > 글 목록
	public function getAdminDocumentsList($id){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board',null]);
		
		$board=\App\Board::where(['id'=>$id,'state'=>200])->first();
		if(!$board) abort(404);
		
		return view('board.admin.document_list',['board'=>$board]);
	}
    
    // 관리자 게시판 > 게시판 관리 > 글 쓰기
	public function getAdminDocumentsCreate($id){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board',null]);
		
		$board=\App\Board::where(['id'=>$id,'state'=>200])->first();
		if(!$board) abort(404);
		
		return view('board.admin.document_create',['board'=>$board]);
	}
	
    // 관리자 게시판 > 게시판 관리 > 글 쓰기
    // [POST] 글 쓰기
	public function postAdminDocumentsCreate(Request $request){
		$board=\App\Board::where(['id'=>$request->board,'state'=>200])->first();
		if(!$board) abort(404);
		
		return $this->postCreate($request,$board->url,$board->domain,true);
	}
	
    // 관리자 게시판 > 게시판 관리 > 글 수정
	public function getAdminDocumentsEdit($id,$document){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board',null]);
		
		$board=\App\Board::where(['id'=>$id,'state'=>200])->first();
		if(!$board) abort(404);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$document,'state'=>200])->first();
		if(!$document) abort(404);
		
		return view('board.admin.document_create',['board'=>$board,'document'=>$document]);
	}
	
    // 관리자 게시판 > 게시판 관리 > 글 수정
    // [POST] 글 수정
	public function postAdminDocumentsEdit(Request $request){
		$board=\App\Board::where(['id'=>$request->board,'state'=>200])->first();
		if(!$board) abort(404);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$request->id,'state'=>200])->first();
		if(!$document) abort(404);
		
		return $this->postEdit($request,$board->url,$board->domain,$document->id,true);
	}
	
    // 관리자 게시판 > 게시판 관리 > 글 삭제
    // [POST] 글 삭제
	public function postAdminDocumentsDelete(Request $request){
		return $this->postAdminDocumentDelete($request,true);
	}
    
    
    // 관리자 게시판 > 게시판 관리 > 게시글 관리
	public function getAdminDocumentList(){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board','document']);
		
		$query=\App\Document::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('name','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('board.admin.list_document',['documents'=>$query]);
	}
	
    // 관리자 게시판 > 게시판 관리 > 게시글 관리
    // [POST] 게시글 삭제
	public function postAdminDocumentDelete(Request $request,$fromBoard=false){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		foreach($request->documents as $id){
			$document=\App\Document::where(['id'=>$id,'state'=>200])->first();
			$document->timestamps=false;
			$document->state=401;
			$document->save();
			$board=$document->board();
			$board->timestamps=false;
			$board->decrement('count_document');
		}
        // 401은 관리자에 의해 삭제된 경우
		
		Controller::notify('게시글을 일괄 삭제했습니다.');
		return redirect(($fromBoard?'/admin/board/'.$request->board.'/documents':'/admin/board/document').($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','게시글을 삭제했습니다.');
	}
    
    
    // 관리자 게시판 > 게시판 관리 > 댓글 관리
	public function getAdminCommentList(){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['board','comment']);
		
		$query=\App\Comment::where('state',200);
		if(isset($_GET['keyword']))
			$query=$query->where('content','like','%'.$_GET['keyword'].'%');
		$query=$query->orderBy('id','desc')->paginate(30);
		
		return view('board.admin.list_comment',['comments'=>$query]);
	}
	
    // 관리자 게시판 > 게시판 관리 > 댓글 관리
    // [POST] 댓글 삭제
	public function postAdminCommentDelete(Request $request){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		foreach($request->documents as $id){
			$comment=\App\Comment::where(['id'=>$id,'state'=>200])->first();
			$comment->timestamps=false;
			$comment->state=401;
			$comment->save();
			$document=$comment->document();
			$document->timestamps=false;
			$document->decrement('count_comment');
		}
        // 401은 관리자에 의해 삭제된 경우
		
		Controller::notify('댓글을 일괄 삭제했습니다.');
		return redirect('/admin/board/comment'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','댓글을 삭제했습니다.');
	}
	
	// 게시판 목록
	public function getList($url,$domain){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		
		$board->timestamps=false;
		$board->increment('count_read');
		
		if(!$board->authority()) return $this->getCreate($url);
		
		return view('board.'.$board->skin.'.list',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board]);
	}
	
	// 게시글 보기
	public function getRead($url,$domain,$id){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$board->authority('read')) abort(401);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$id,'state'=>200])->first();
		if(!$document) abort(404);
		
		$document->timestamps=false;
		$document->increment('count_read');
		
		return view('board.'.$board->skin.'.read',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board,'document'=>$document]);
	}
	
	// 게시글 쓰기
	public function getCreate($url,$domain){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$board->authority('document')) abort(401);
		
		return view('board.'.$board->skin.'.create',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board]);
	}
	
	// 게시글 쓰기
	// [POST] 게시글 쓰기
	public function postCreate(Request $request,$url,$domain,$fromAdmin=false){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$fromAdmin)
			if(!$board->authority('document')) abort(401);
		
		$id=Controller::getSequence();
		
		\App\Document::create([
			'id'=>$id,
			'board'=>$board->id,
			'category'=>$request->category,
			'author'=>Auth::check()?Auth::user()->id:null,
			'title'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->title_real):$request->title_real,
			'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content,
			'notice'=>$request->notice?true:false,
			'secret'=>$request->secret?true:false,
			'allow_comment'=>$request->allow_comment?true:false,
			'state'=>200,
			'ip_address'=>$request->ip(),
		]);
		
		if(count($board->extravars())){
			foreach($board->extravars() as $extravar){
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
				
				DB::table('board_document_extravars')->insert([
					'extravar'=>$extravar->id,
					'document'=>$id,
					'board'=>$board->id,
					'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}
		
		if(null!==$request->attach_dropzone)
			foreach($request->attach_dropzone as $attach)
				DB::table('files')->where('name',$attach)->update(['article'=>$id]);
		
		$board->timestamps=false;
		$board->increment('count_document');
		
		if($fromAdmin)
			$redirect='/admin/board/'.$board->id.'/documents';
		else 
			if($board->authority('read'))
				$redirect='/'.$board->url.'/'.$id;
			else
				$redirect='/'.$board->url.'/complete';
		
		$document=\App\Document::find($id);
		
		// 메일 발송
		$mail_content='';
		foreach($board->extravars() as $extravar){
			$mail_content.='<div class="question">'.(\App\Encryption::checkEncrypted($extravar->name)?\App\Encryption::decrypt($extravar->name):$extravar->name).'</div><p>';
			if($extravar->type=='text'){
				if($document->extravar($extravar->id))
					 $mail_content.=htmlspecialchars($document->extravar($extravar->id));
			}elseif($extravar->type=='textarea'){
				if($document->extravar($extravar->id))
					$mail_content.=str_replace('&lt;br /&gt;','<br>',htmlspecialchars(nl2br($document->extravar($extravar->id))));
			}elseif($extravar->type=='radio'){
				if($document->extravar($extravar->id))
					$mail_content.=htmlspecialchars($document->extravar($extravar->id));
			}elseif($extravar->type=='checkbox'){
				if(count($document->extravar($extravar->id)))
					$mail_content.=htmlspecialchars(implode(', ',$document->extravar($extravar->id)));
			}elseif($extravar->type=='order'){
				if(count($document->extravar($extravar->id)))
					$mail_content.=htmlspecialchars(implode(', ',$document->extravar($extravar->id)));
			}elseif($extravar->type=='image'){
				if($document->extravar($extravar->id))
				$mail_content.='<img src="'.htmlspecialchars(url($document->extravar($extravar->id))).'" alt="">';
			}elseif($extravar->type=='file'){
				if($document->extravar($extravar->id))
					$mail_content.='.💾 '.htmlspecialchars(\App\File::where('name',str_replace('/file/','',$document->extravar($extravar->id)))->first()->original);
			}
			$mail_content.='&nbsp;</p>';
		}
		$mail_content.='<div class="question">내용</div>';
		$mail_content.=$document->content();
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		
		foreach($board->mailing_list() as $email){
			AdminController::sendmail($email,'['.\App\Setting::find('app_name')->content.'] '.$board->name.'에 새로운 게시글: '.$request->title_real,'<a href="'.$board->url().'">'.$board->name.'</a> 게시판에 <a href="'.url($board->url.'/'.$id).'">'.$request->title_real.'</a> 게시글이 새로 작성되었습니다. '.($mail_content?'<div class="content">'.$mail_content.'</div>':''));
		}
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 <u>'.$request->title_real.'</u> 게시글을 작성했습니다.');
		return redirect($redirect.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
	
	// 게시글 쓰기 - 게시글 열람 권한 없을 때 오는 작성 완료 페이지
	public function getComplete($url,$domain){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		
		return view('board.'.$board->skin.'.complete',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board]);
	}
	
	// 게시글 수정
	public function getEdit($url,$domain,$id){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$board->authority('document')) abort(401);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$id,'state'=>200])->first();
		if(!$document) abort(404);
		if(!$document->isMine()) abort(404);
		
		return view('board.'.$board->skin.'.create',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board,'document'=>$document]);
	}
	
	// 게시글 수정
	// [POST] 게시글 수정
	public function postEdit(Request $request,$url,$domain,$id,$fromAdmin=false){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$fromAdmin)
			if(!$board->authority('document')) abort(401);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$id,'state'=>200])->first();
		if(!$document) abort(404);
		if(!$fromAdmin)
			if(!$document->isMine()) abort(404);
		
		$document->category=$request->category;
		$document->title=\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->title_real):$request->title_real;
		$document->content=\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content;
		$document->notice=$request->notice?true:false;
		$document->secret=$request->secret?true:false;
		$document->allow_comment=$request->allow_comment?true:false;
		$document->save();
		
		DB::table('board_document_extravars')->where('document',$document->id)->delete();
		if(count($board->extravars())){
			foreach($board->extravars() as $extravar){
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
				
				DB::table('board_document_extravars')->insert([
					'extravar'=>$extravar->id,
					'document'=>$id,
					'board'=>$board->id,
					'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($content):$content,
				]);
			}
		}
		
		if(null!==$request->attach_dropzone){
			if($document->files())
				foreach($document->files() as $attach){
					if(!in_array($attach->name,$request->attach_dropzone)){
						\Storage::delete($attach->name);
						DB::table('files')->where('name',$attach->name)->update(['state'=>400]);
					}
				}
		
			foreach($request->attach_dropzone as $attach)
				DB::table('files')->where('name',$attach)->update(['article'=>$id]);
		}else{
			if($document->files())
				foreach($document->files() as $attach){
					\Storage::delete($attach->name);
					DB::table('files')->where('name',$attach->name)->update(['state'=>400]);
				}
		}
		
		if($fromAdmin)
			$redirect='/admin/board/'.$board->id.'/documents';
		else 
			if($board->authority('read'))
				$redirect='/'.$board->url.'/'.$id;
			else
				$redirect='/'.$board->url.'/complete';
				
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 '.($document->title!=$request->title_real?'<u>'.$document->title.'</u> → ':'').'<u>'.$request->title_real.'</u> 게시글을 수정했습니다.');
		return redirect($redirect.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
    
    // 게시글 삭제
    // [POST] 게시글 삭제
	public function postDelete(Request $request,$url,$domain,$id){
		Controller::logActivity('USR');
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$board->authority('document')) abort(401);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$id,'state'=>200])->first();
		if(!$document) abort(404);
		if(!$document->isMine()) abort(404);
		
		$document->state=400;
		$document->save();
		
		$board->timestamps=false;
		$board->decrement('count_document');
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 <u>'.$document->title.'</u> 게시글을 삭제했습니다.');
		return redirect('/'.$board->url.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','게시글을 삭제했습니다.');
	}
    
    // 댓글 쓰기
    // [POST] 댓글 쓰기
	public function postComment(Request $request,$url,$domain,$id){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$board=\App\Board::where(['url'=>$url,'domain'=>$domain,'state'=>200])->first();
		if(!$board) abort(404);
		if(!$board->authority('comment')) abort(401);
		
		$document=\App\Document::where(['board'=>$board->id,'id'=>$id,'state'=>200])->first();
		if(!$document) abort(404);
		if(!$document->allow_comment) abort(401);
		
		$id=Controller::getSequence();
		
		\App\Comment::create([
			'id'=>$id,
			'board'=>$board->id,
			'document'=>$document->id,
			'author'=>Auth::check()?Auth::user()->id:null,
			'content'=>\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content,
			'notice'=>$request->notice?true:false,
			'secret'=>$request->secret?true:false,
			'state'=>200,
			'ip_address'=>$request->ip(),
		]);
		
		if(null!==$request->attach_dropzone)
			foreach($request->attach_dropzone as $attach)
				DB::table('files')->where('name',$attach)->update(['article'=>$id]);
		
		$document->timestamps=false;
		$document->increment('count_comment');
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 <u>'.$document->title.'</u> 게시글에 댓글을 작성했습니다.');
		return redirect('/'.$board->url.'/'.$document->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'').'#comment'.$id);
	}
	
	// 댓글 수정
	public function getCommentEdit($id){
		Controller::logActivity('USR');
		
		$comment=\App\Comment::where(['id'=>$id,'state'=>200])->first();
		if(!$comment) abort(404);
		if(!$comment->isMine()) abort(404);
		
		$board=$comment->board();
		if(!$board) abort(404);
		if(!$board->authority('comment')) abort(401);
		
		$document=$comment->document();
		if(!$document) abort(404);
		if(!$document->allow_comment) abort(401);
		
		return view('board.'.$board->skin.'.comment',['layout'=>$board->layout?\App\Layout::find($board->layout):null,'board'=>$board,'document'=>$document,'comment'=>$comment]);
		
	}
	
	// 댓글 수정
	// [POST] 댓글 수정
	public function postCommentEdit(Request $request,$id){
		Controller::logActivity('USR');
		
		if($request->title) abort(418); // 자동 입력 로봇들을 방지함
		
		$comment=\App\Comment::where(['id'=>$id,'state'=>200])->first();
		if(!$comment) abort(404);
		if(!$comment->isMine()) abort(404);
		
		$board=$comment->board();
		if(!$board) abort(404);
		if(!$board->authority('comment')) abort(401);
		
		$document=$comment->document();
		if(!$document) abort(404);
		
		$comment->content=\App\Encryption::isEncrypt('board')?\App\Encryption::encrypt($request->content):$request->content;
		$comment->notice=$request->notice?true:false;
		$comment->secret=$request->secret?true:false;
		$comment->save();
		
		if(null!==$request->attach_dropzone){
			if($comment->files())
				foreach($comment->files() as $attach){
					if(!in_array($attach->name,$request->attach_dropzone)){
						\Storage::delete($attach->name);
						DB::table('files')->where('name',$attach->name)->delete();
					}
				}
		
			foreach($request->attach_dropzone as $attach)
				DB::table('files')->where('name',$attach)->update(['article'=>$id]);
		}else{
			if($comment->files())
				foreach($comment->files() as $attach){
					\Storage::delete($attach->name);
					DB::table('files')->where('name',$attach->name)->delete();
				}
		}
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 <u>'.$document->title.'</u> 게시글의 댓글을 수정했습니다.');
		return redirect('/'.$board->url.'/'.$document->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'').'#comment'.$id);
	}
    
    // 댓글 삭제
    // [POST] 댓글 삭제
	public function postCommentDelete(Request $request,$id){
		Controller::logActivity('USR');
		
		$comment=\App\Comment::where(['id'=>$id,'state'=>200])->first();
		if(!$comment) abort(404);
		if(!$comment->isMine()) abort(404);
		
		$board=$comment->board();
		if(!$board) abort(404);
		if(!$board->authority('comment')) abort(401);
		
		$document=$comment->document();
		if(!$document) abort(404);
		
		$comment->state=400;
		$comment->save();
		
		$document->timestamps=false;
		$document->decrement('count_comment');
		
		$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
		$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
		
		Controller::notify('<u>'.$board->name.'</u> 게시판의 <u>'.$document->title.'</u> 게시글의 댓글을 삭제했습니다.');
		return redirect('/'.$board->url.'/'.$document->id.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
	
	// 카드 - 게시판 방문 수
	static public function cardCountread(){
		$boards=\App\Board::where('state',200)->orderBy('count_read','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/board').'">게시판 방문 수</a></h4><ul>';
		foreach($boards as $board){
			$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
			$content.='<li><a href="'.url('/admin/board/'.$board->id).'">'.$board->name.'&nbsp;<span>'.$board->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 게시판 게시글 수
	static public function cardCountdocument(){
		$boards=\App\Board::where('state',200)->orderBy('count_document','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/board').'">게시판 게시글 수</a></h4><ul>';
		foreach($boards as $board){
			$board->name=\App\Encryption::checkEncrypted($board->name)?\App\Encryption::decrypt($board->name):$board->name;
			$content.='<li><a href="'.url('/admin/board/'.$board->id).'">'.$board->name.'&nbsp;<span>'.$board->count_document.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 게시글 조회 수
	static public function cardCountreadDocument(){
		$documents=\App\Document::where('state',200)->orderBy('count_read','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/board').'">게시글 조회 수</a></h4><ul>';
		foreach($documents as $document){
			$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
			$content.='<li><a href="'.url('/'.$document->board()->url().'/'.$document->id).'" target="_blank">'.$document->title.'&nbsp;<span>'.$document->count_read.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
	// 카드 - 게시글 댓글 수
	static public function cardCountcommentDocument(){
		$documents=\App\Document::where('state',200)->orderBy('count_comment','desc')->limit(10)->get();
		
		$content='<div class="card_list"><h4><a href="'.url('/admin/board').'">게시글 댓글 수</a></h4><ul>';
		foreach($documents as $document){
			$document->title=\App\Encryption::checkEncrypted($document->title)?\App\Encryption::decrypt($document->title):$document->title;
			$content.='<li><a href="'.url('/'.$document->board()->url().'/'.$document->id).'" target="_blank">'.$document->title.'&nbsp;<span>'.$document->count_comment.'</span></a><div class="clear"></div></li>';
		}
		$content.='</ul></div>';
		
		return $content;
	}
	
}