<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;

use Auth;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	
    public static function getSequence(){
	   return \DB::table('sequence')->insertGetId([]);
    }
    
    public static function filterHTML($content){
		$content=preg_replace('#<script(.*?)>(.*?)</script>#is','&lt;script$1>$2&lt;/script>',$content);
		
		$content=preg_replace_callback("/([^a-z])(o)(n)/i",
		create_function('$matches', 'if($matches[2]=="o") $matches[2] = "&#111;";
		else $matches[2] = "&#79;"; return $matches[1].$matches[2].$matches[3];'), $content);
		
		$content=preg_replace('#j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:#i','javascript&#760;',$content);
		$content=str_replace('data:','data&#760;',$content);
		
		return $content;
    }
    
    public static function logActivity($type,$message=''){
	    \DB::table('log_activity')->insert([
	    	'type'=>$type,
	    	'user'=>\Auth::check()?\Auth::user()->id:null,
	    	'method'=>\Request::method(),
	    	'message'=>$message?$message.'
':''.json_encode(\Request::except(['_token','password'])),
	    	'url'=>\Request::fullUrl(),
	    	'referer'=>\Request::server('HTTP_REFERER'),
	    	'user_agent'=>\Request::header('user-agent'),
	    	'ip_address'=>\Request::ip(),
	    ]);
    }
    
    public static function notify($message='',$user=null){
	    $author=Auth::check()?Auth::user()->id:null;
	    if(!$user) $user=$author;
	    
	    \DB::table('notifications')->insert([
	    	'author'=>$author,
	    	'user'=>$user,
	    	'message'=>$message,
	    ]);
    }
	
	public static function getVersion(){
		return \File::get(base_path().'/version');
	}
    
    public function getListFromUrl($url=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    if(!method_exists($object,'getList')) abort(404);
	    return $object->getList($module->id);
    }
    
    public function getReadFromUrl($url='',$id=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    if(!method_exists($object,'getRead')) abort(404);
	    return $object->getRead($module->id,$id);
    }
    
    public function getActionFromUrl($url='',$action=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='get'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($module->id);
    }
    
    public function postActionFromUrl(Request $request,$url='',$action=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='post'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($request,$module->id);
    }
    
    public function getActionFromUrlWithId($url='',$id='',$action=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='get'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($module->id,$id);
    }
    
    public function postActionFromUrlWithId(Request $request,$url='',$id='',$action=''){
	    $module=DB::table('ids')->where('id',$url)->first();
	    if(!$module) abort(404);
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='post'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($request,$module->id,$id);
    }
}
