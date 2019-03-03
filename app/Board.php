<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Board extends Model
{
    protected $table = 'boards';
    protected $guarded = [];
    
    public function notices($category=null){
	    if($category)
	    	$_GET['category']=$category;
	    $documents=Document::where(['board'=>$this->id,'state'=>200,'notice'=>true]);
	    if(isset($_GET['category']))
	    	$documents=$documents->where('category',$_GET['category']);
	    if(isset($_GET['keyword']))
	    	$documents=$documents->where('title','like','%'.$_GET['keyword'].'%');
	    $documents=$documents->orderBy($this->sort_by,$this->sort_order)->get();
	    return $documents;
    }
    
    public function documents($count=30,$category=null){
	    if($category)
	    	$_GET['category']=$category;
	    $documents=Document::where(['board'=>$this->id,'state'=>200,'notice'=>false]);
	    if(isset($_GET['category']))
	    	$documents=$documents->where('category',$_GET['category']);
	    if(isset($_GET['keyword']))
	    	$documents=$documents->where('title','like','%'.$_GET['keyword'].'%');
	    $documents=$documents->orderBy($this->sort_by,$this->sort_order)->paginate(count($this->notices())>($count-5)?5:$count-count($this->notices()));
	    return $documents;
    }
    
    public function categories(){
	    return DB::table('board_categories')->where(['board'=>$this->id,'state'=>200])->orderBy('order_show')->get();
    }
    
    public function extravars(){
	    return DB::table('board_extravars')->where(['board'=>$this->id,'state'=>200])->orderBy('order_show')->get();
    }
    
    public function groups(){
	    if(!$this->allowed_group) return [];
	    return explode('|',$this->allowed_group);
    }
    
    public function groups_read(){
	    if(!$this->allowed_group_read) return [];
	    return explode('|',$this->allowed_group_read);
    }
    
    public function groups_document(){
	    if(!$this->allowed_group_document) return [];
	    return explode('|',$this->allowed_group_document);
    }
    
    public function groups_comment(){
	    if(!$this->allowed_group_comment) return [];
	    return explode('|',$this->allowed_group_comment);
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
	    else if($type=='document')
	    	$groups=$this->groups_document();
	    else if($type=='comment')
	    	$groups=$this->groups_comment();
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