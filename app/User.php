<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable=['id','name','nickname','email','password','state','note'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden=['password','remember_token'];
    
    public function groups(){
	    $groups=[];
	    $data=DB::table('users_groups')->where('user',$this->id)->get();
	    foreach($data as $datum){
		    $group=DB::table('users_group')->where(['id'=>$datum->group,'state'=>200])->first();
		    if($group)
		    	$groups[$datum->group]=$group->name;
	    }
	    return $groups;
    }
    
    public function thumbnail(){
		$data=DB::table('files')->where(['article'=>$this->id,'type'=>'image'])->orderBy('id','desc')->first();
		if(!$data) return '';
		
		return '/user/profile/'.$this->id;
    }
    
    public static function extravars(){
	    return DB::table('user_extravar')->where(['state'=>200])->orderBy('order_show')->get();
    }
    
    public function extravar($id){
	    $query=DB::table('user_extravars')->where(['extravar'=>$id,'user'=>$this->id])->first();
	    $extravar=DB::table('user_extravar')->where(['id'=>$id])->first();
	    if(!$query||!$query->content) return $extravar->type=='checkbox'||$extravar->type=='order'?[]:null;
	    
	    $query->content=\App\Encryption::checkEncrypted($query->content)?\App\Encryption::decrypt($query->content):$query->content;
	    
	    if($extravar->type=='checkbox'||$extravar->type=='order')
	    	return explode('|',$query->content);
	    
	    return $query->content;
    }
}
