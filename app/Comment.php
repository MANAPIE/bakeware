<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Comment extends Model
{
    protected $table = 'board_document_comments';
    protected $guarded = [];
    
    public function board(){
	    return \App\Board::find($this->board);
    }
    
    public function document(){
	    return \App\Document::find($this->document);
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
	    $files=\App\File::where(['article'=>$this->id,'type'=>'dropzone'])->get();
	    if(!$files||!count($files)) return null;
	    return $files;
    }
    
    public function summary($length=100){
	    $string=trim(str_replace('&nbsp;','',strip_tags($this->content)));
	    
	    if(strlen($string)==0)
	    	return '<span style="color:#999">(글자가 없는 댓글)</span>';
	    
	    return mb_substr($string,0,$length).(strlen($string)>$length?'...':'');
    }
}