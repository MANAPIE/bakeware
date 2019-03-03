<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Answer extends Model
{
    protected $table = 'form_answers';
    protected $guarded = [];
    
    public function form(){
	    return \App\Form::find($this->form);
    }
    
    public function author(){
	    if(!$this->author) return null;
	    $user=\App\User::find($this->author);
	    if(!$user) return null;
	    return $user;
    }
    
    public function isMine(){
	    return \Auth::check()&&(\Auth::user()->id==$this->author||array_key_exists(2,\Auth::user()->groups()));
    }
    
    public function item($id){
	    $query=DB::table('form_answer_items')->where(['question'=>$id,'answer'=>$this->id,'state'=>200])->first();
	    $extravar=DB::table('form_questions')->where(['id'=>$id,'form'=>$this->form])->first();
	    if(!$query||!$query->content) return $extravar->type=='checkbox'||$extravar->type=='order'?[]:null;
	    
	    $query->content=\App\Encryption::checkEncrypted($query->content)?\App\Encryption::decrypt($query->content):$query->content;
	    
	    if($extravar->type=='checkbox'||$extravar->type=='order')
	    	return explode('|',$query->content);
	    
	    return $query->content;
    }
    
}