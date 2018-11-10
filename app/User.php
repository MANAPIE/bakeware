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
    protected $fillable=['id','name','nickname','email','password','state'];

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
}
