<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Gallery extends Model
{
    protected $table = 'galleries';
    protected $guarded = [];
    
    public function url(){
	    return url(($this->domain?'//'.$this->domain:'').'/'.$this->url);
    }
    
    public function cadres($count=30,$category=null){
	    if($category)
	    	$_GET['category']=$category;
	    $cadres=Cadre::where(['gallery'=>$this->id,'state'=>200]);
	    if(isset($_GET['category']))
	    	$cadres=$cadres->where('category',$_GET['category']);
	    if(isset($_GET['keyword']))
	    	$cadres=$cadres->where('title','like','%'.$_GET['keyword'].'%');
	    $cadres=$cadres->orderBy('order_show','desc');
	    if($count)
	    	$cadres=$cadres->paginate($count);
	    else
	    	$cadres=$cadres->get();
	    return $cadres;
    }
    
    public function categories(){
	    return DB::table('gallery_categories')->where(['gallery'=>$this->id,'state'=>200])->orderBy('order_show')->get();
    }
    
    public function extravars(){
	    return DB::table('gallery_extravars')->where(['gallery'=>$this->id,'state'=>200])->orderBy('order_show')->get();
    }
    
    public function groups(){
	    if(!$this->allowed_group) return [];
	    return explode('|',$this->allowed_group);
    }
    
    public function groups_read(){
	    if(!$this->allowed_group_read) return [];
	    return explode('|',$this->allowed_group_read);
    }
    
    public function groups_cadre(){
	    if(!$this->allowed_group_cadre) return [];
	    return explode('|',$this->allowed_group_cadre);
    }
    
    public function groups_mail(){
	    if(!$this->allowed_group_mail) return [];
	    return explode('|',$this->allowed_group_mail);
    }
    
    public function mailing_list(){
		$users=[];
		foreach($this->groups_mail() as $group){
			if(DB::table('users_group')->where(['id'=>$group,'state'=>200])->first()){
				$query=DB::table('users_groups')->where('group',$group)->get();
				if($query){
					foreach($query as $u){
						$user=\App\User::where(['id'=>$u->user,'state'=>200])->first();
						if($user&&$user->email){
							$users[$user->id]=(\App\Encryption::checkEncrypted($user->email)?\App\Encryption::decrypt($user->email):$user->email);
						}
					}
				}
			}
		}
		return $users;
    }
    
    public function authority($type=null){
	    if($type=='read')
	    	$groups=$this->groups_read();
	    else if($type=='cadre')
	    	$groups=$this->groups_cadre();
	    else
	    	$groups=$this->groups();
	    
		$allowed=true;
	    if(count($groups)){
		    $allowed=false;
		    if(\Auth::check()){
			    foreach($groups as $group){
				    if(array_key_exists($group,\Auth::user()->groups())){
					    $allowed=true;
					    break;
				    }
			    }
			}
		}
		
		return $allowed;
    }
}