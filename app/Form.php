<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Form extends Model
{
    protected $table = 'forms';
    protected $guarded = [];
    
    public function url(){
	    return url(($this->domain?'//'.$this->domain:'').'/'.$this->url);
    }
    
    public function inPeriod(){
	    $start_at=strtotime($this->start_at);
	    $end_at=strtotime($this->end_at);
	    $now=strtotime(now());
	    if($start_at<=$now&&$now<=$end_at) return true;
	    return false;
    }
    
    public function answers(){
	    $answers=Answer::where(['form'=>$this->id,'state'=>200])->paginate(30);
		return $answers;
    }
    
    public function questions(){
	    return DB::table('form_questions')->where(['form'=>$this->id,'state'=>200])->orderBy('order_show')->get();
    }
    
    public function question($id){
	    return DB::table('form_questions')->where(['form'=>$this->id,'id'=>$id,'state'=>200])->first();
    }
    
    public function question_statistic($id){
	    $question=$this->question($id);
	    $query=DB::table('form_answer_items')->where(['form'=>$this->id,'question'=>$id,'state'=>200])->orderBy('created_at')->get();
	    $answers=[];
	    foreach($query as $answer){
		    $answer->content=\App\Encryption::checkEncrypted($answer->content)?\App\Encryption::decrypt($answer->content):$answer->content;
		    if($question->type=='checkbox'){
				if($answer->content){
					foreach(explode('|',$answer->content) as $val){
						if(isset($answers[$val]))
						    $answers[$val]++;
						else
							$answers[$val]=1;
					}
			    }
			    
		    }else{
			    if(isset($answers[$answer->content]))
			    	$answers[$answer->content]++;
			    else
				    $answers[$answer->content]=1;
			}
	    }
	    
	    return $answers;
	    //if($extravar->type=='checkbox'||$extravar->type=='order')
	    //	return explode('|',$query->content);
	    
	    
    }
    
    public function groups(){
	    if(!$this->allowed_group) return [];
	    return explode('|',$this->allowed_group);
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
    
    public function authority(){
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