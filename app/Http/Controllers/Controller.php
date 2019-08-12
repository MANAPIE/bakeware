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
    
    public static function filterHTML($data){
	    
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
		
		// Remove javascript:, vbscript:, data:, -moz-binding: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2javascript&#760;', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2vbscript&#760;', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2-moz-binding&#760;', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*d[\x00-\x20]*a[\x00-\x20]*t[\x00-\x20]*a[\x00-\x20]*:#iu', '$1=$2data&#760;', $data);
		
		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
		
		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
		
		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);
		
		return $data;
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
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    
	    $object=new $class();
	    if(!method_exists($object,'getList')) abort(404);
	    return $object->getList(ltrim($module->id,$host));
    }
    
    public function getReadFromUrl($url='',$id=''){
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    if(!method_exists($object,'getRead')) abort(404);
	    return $object->getRead(ltrim($module->id,$host),$id);
    }
    
    public function getActionFromUrl($url='',$action=''){
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='get'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function(ltrim($module->id,$host));
    }
    
    public function postActionFromUrl(Request $request,$url='',$action=''){
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='post'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($request,ltrim($module->id,$host));
    }
    
    public function getActionFromUrlWithId($url='',$id='',$action=''){
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
	    
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='get'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function(ltrim($module->id,$host),$id);
    }
    
    public function postActionFromUrlWithId(Request $request,$url='',$id='',$action=''){
	    $host=request()->getHost().'/';
	    $module=DB::table('ids')->where('id',$host.$url)->first();
	    if(!$module){
			$host=ltrim($host,'www.');
		    $module=DB::table('ids')->where('id',$host.$url)->first();
		    if(!$module){
			    $host='/';
		    	$module=DB::table('ids')->where('id',$host.$url)->first();
		    	if(!$module)
			    	if(!$url) return view('welcome');
			    	else abort(404);
		    }
	    }
		
	    $class='\\App\\Http\\Controllers\\'.ucfirst($module->module).'Controller';
	    $object=new $class();
	    $function='post'.ucfirst($action);
	    if(!method_exists($object,$function)) abort(404);
	    return $object->$function($request,ltrim($module->id,$host),$id);
    }
}
