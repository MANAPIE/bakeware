<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Document extends Model
{
    protected $table = 'board_documents';
    protected $guarded = [];
    
    public function board(){
	    return \App\Board::find($this->board);
    }
    
    public function content(){
	    return \App\Http\Controllers\Controller::filterHTML(\App\Encryption::checkEncrypted($this->content)?\App\Encryption::decrypt($this->content):$this->content);
    }
    
    public function author(){
	    if(!$this->author) return null;
	    $user=\App\User::find($this->author);
	    if(!$user) return null;
	    return $user;
    }
    
    public function category(){
	    if(!$this->category) return null;
	    $category=DB::table('board_categories')->where(['board'=>$this->board,'id'=>$this->category])->first();
	    if(!$category) return null;
	    return $category;
    }
    
    public function isMine(){
	    return \Auth::check()&&(\Auth::user()->id==$this->author||array_key_exists(2,\Auth::user()->groups()));
    }
    
    public function files(){
	    $files=\App\File::where(['article'=>$this->id,'type'=>'dropzone','state'=>200])->get();
	    if(!$files||!count($files)) return null;
	    return $files;
    }
    
    public function comments($notice=false){
	    $comments=\App\Comment::where(['document'=>$this->id,'state'=>200,'notice'=>$notice])->orderBy('created_at','asc')->get();
	    if(!$comments) return null;
	    return $comments;
    }
    
    public function summary($length=100){
	    $string=trim(str_replace('&nbsp;','',strip_tags(\App\Encryption::checkEncrypted($this->content)?\App\Encryption::decrypt($this->content):$this->content)));
	    
	    if(mb_strlen($string)==0)
	    	return '<span style="color:#999">(글자가 없는 게시글)</span>';
	    
	    return mb_substr($string,0,$length).(mb_strlen($string)>$length?'...':'');
    }
    
    public function extravar($id){
	    $query=DB::table('board_document_extravars')->where(['extravar'=>$id,'document'=>$this->id])->first();
	    $extravar=DB::table('board_extravars')->where(['id'=>$id,'board'=>$this->board])->first();
	    if(!$query||!$query->content) return $extravar->type=='checkbox'||$extravar->type=='order'?[]:'';
	    
	    $query->content=\App\Encryption::checkEncrypted($query->content)?\App\Encryption::decrypt($query->content):$query->content;
	    
	    if($extravar->type=='checkbox'||$extravar->type=='order')
	    	return explode('|',$query->content);
	    
	    return $query->content;
    }
    
}