<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;

use Auth;
use DB;
use Storage;
use File;
use Response;

class ResourceController extends Controller {
	
	static public function routes(){
		\Route::get('/image/{name}','ResourceController@getImageResource')->where('name','(.*)');
		\Route::get('/style/{name}','ResourceController@getStyleResource')->where('name','(.*)');
		\Route::get('/font/{name}','ResourceController@getFontResource')->where('name','(.*)');
		\Route::get('/script/{name}','ResourceController@getScriptResource')->where('name','(.*)');
	
		\Route::post('/upload/dropzone','ResourceController@postUploadDropzone');
		\Route::post('/upload/file','ResourceController@postUploadFile');
		\Route::get('/file/{name}','ResourceController@getDownloadFile');
		\Route::post('/upload/image','ResourceController@postUploadImage');
		\Route::get('/file/image/{name}','ResourceController@getDownloadImage');
		
		\Route::get('/admin/resource','ResourceController@getAdminList');
		\Route::post('/admin/resource/delete','ResourceController@postAdminDelete');
	}
	
	public function getImageResource($name){
		$path=base_path().'/resources/views/_images/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		$response=Response::make($file,200);
	//	$response->withHeaders(['Content-Type'=>$type]);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;

	}
	
	public function getStyleResource($name){
		$path=base_path().'/resources/views/_styles/'.$name;
		if(!File::exists($path)) $path=base_path().'/resources/views/layout/'.str_replace('.css','',$name).'/style.css';
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type]);
	//	$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	public function getFontResource($name){
		$path=base_path().'/resources/views/_styles/'.$name;
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type=File::mimeType($path);
		if($type=='text/plain')
			$type='text/css';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=31536000']);
		return $response;
	}
	
	public function getScriptResource($name){
		$path=base_path().'/resources/views/_scripts/'.$name;
		if(!File::exists($path)) $path=base_path().'/resources/views/layout/'.str_replace('.js','',$name).'/script.js';
		if(!File::exists($path)) abort(404);
		$file=File::get($path);
		$type='text/javascript';
		$response=Response::make($file,200);
		$response->withHeaders(['Content-Type'=>$type]);
	//	$response->withHeaders(['Content-Type'=>$type,'Cache-Control'=>'public,max-age=86400']);
		return $response;
	}
	
	public function postUploadFile(Request $request){
		Controller::logActivity('USR');
		
		$file=$request->file('file');
		if(!$request->hasFile('file')) return response()->json('파일이 없습니다.',422);
		
		$filename=ResourceController::saveFile('file',$file);
		
	    return response()->json([
		    'name'=>$filename,
		    'type'=>$file->getClientMimeType(),
		    'url'=>$filename,
	    ]);
	}
	
	public function postUploadDropzone(Request $request){
		Controller::logActivity('USR');
		
		$file=$request->file('file');
		if(!$request->hasFile('file')) return response()->json('파일이 없습니다.',422);
		
		$filename=ResourceController::saveFile('dropzone',$file);
		
	    return response()->json([
		    'name'=>$filename,
		    'type'=>$file->getClientMimeType(),
		    'url'=>$filename,
	    ]);
	}
	
	public function postUploadImage(Request $request){
		Controller::logActivity('USR');
		
		if($request->hasFile('file')){
			$file=$request->file('file');
			
		}else{
			if($request->hasFile('upload')){
				$file=$request->file('upload');
			}else{
				return response()->json('파일이 없습니다.',422);
			}
		}
		
		$filename=ResourceController::saveFile('image',$file);
		
	    return response()->json([
		    'uploaded'=>1,
		    'name'=>$filename,
		    'type'=>$file->getClientMimeType(),
		    'url'=>$filename,
	    ]);
	}
	
	public static function saveFile($type,$file,$article=null){
		if($type=='image'){
			$allowedMimeTypes=['image/jpeg','image/gif','image/png','image/bmp','image/svg+xml'];
			if(!in_array($file->getClientMimeType(),$allowedMimeTypes)) return response()->json('이미지가 아닙니다.',406);
		}
		
		$filename=rand(100,999).uniqid();
		Storage::put($filename,file_get_contents($file->getRealPath()));
		
	    $id=Controller::getSequence();
	    
		DB::table('files')->insert([
			'id'=>$id,
			'article'=>$article,
			'author'=>(Auth::check()?Auth::user()->id:null),
			'original'=>$file->getClientOriginalName(),
			'name'=>$filename,
			'extension'=>$file->getClientOriginalExtension(),
			'mime'=>$file->getClientMimeType(),
			'size'=>Storage::size($filename),
			'type'=>$type,
			'state'=>200,
			'count_download'=>0,
			'created_at'=>DB::raw('CURRENT_TIMESTAMP'),
		]);
		
		return ($type=='dropzone'?'':'/file/').($type=='image'?'image/':'').$filename;
	}
	
	public function getDownloadFile($name){
		Controller::logActivity('USR');
		
		$data=DB::table('files')->where(['name'=>$name,'state'=>200])->where('type','!=','image')->first();
		if($data==null){
			return abort(404);
		}
		
		$file=Storage::get($name);
		DB::table('files')->where('name',$name)->increment('count_download');
		return response($file, 200)->header('Pragma','public')->header('Expires','0')->header('Content-Type','application/octet-stream')->header('Content-Disposition','attachment; filename='.$data->original)->header('Content-Transfer-Encoding','binary')->header('Content-Length',$data->size);
	}
	
	public function getDownloadImage($name){
		Controller::logActivity('USR');
		
		$data=DB::table('files')->where(['name'=>$name,'state'=>200,'type'=>'image'])->first();
		if($data==null){
			return abort(404);
		}
		
		$file=Storage::get($name);
		DB::table('files')->where('name',$name)->increment('count_download');
		return response($file, 200)->withHeaders(['Content-Type'=>$data->mime,'Cache-Control'=>'public,max-age=3600']);
	}
	
    public static function staticRemoveImage($name){
	    if(DB::table('files')->where(['name'=>$name,'type'=>'image'])->first()){
			DB::table('files')->where(['name'=>$name,'type'=>'image'])->update([
				'state'=>400
			]);
			Storage::delete($name);
		}
    }
    
    // 관리자 첨부파일 > 첨부파일 관리
	public function getAdminList(){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		View::share('current',['resource',null]);
		
		$query=\App\File::orderBy('id','desc');
		if(isset($_GET['keyword']))
			$query=$query->where('original','like','%'.$_GET['keyword'].'%');
		$query=$query->paginate(30);
		
		return view('admin.resource',['resources'=>$query]);
	}
    
    // 관리자 첨부파일 > 첨부파일 관리
    // [POST] 첨부파일 삭제
	public function postAdminDelete(Request $request){
		Controller::logActivity('USR');
		BoardController::checkAuthority();
		
		foreach($request->resources as $id){
			$resource=\App\File::where(['id'=>$id,'state'=>200])->first();
			$resource->timestamps=false;
			$resource->state=401;
			$resource->removed_at=date('Y-m-d H:i:s');
			$resource->save();
			Storage::delete($resource->name);
		}
        // 401은 관리자에 의해 삭제된 경우
		
		Controller::notify('파일을 일괄 삭제했습니다.');
		return redirect('/admin/resource/'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''))->with('message','파일을 삭제했습니다.');
	}
	
}