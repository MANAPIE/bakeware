<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Cadre extends Model
{
    protected $table = 'gallery_cadres';
    protected $guarded = [];
    
    public function gallery(){
	    return \App\Gallery::find($this->gallery);
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
	    $category=DB::table('gallery_categories')->where(['gallery'=>$this->gallery,'id'=>$this->category])->first();
	    if(!$category) return null;
	    return $category;
    }
    
    public function isMine(){
	    return \Auth::check()&&(\Auth::user()->id==$this->author||array_key_exists(2,\Auth::user()->groups()));
    }
    
    public function files(){
	    $files=\App\File::where(['article'=>$this->id,'type'=>'image','state'=>200])->orderBy('order_show','asc')->get();
	    if(!$files||!count($files)) return null;
	    return $files;
    }
    
    public function summary($length=100){
	    $string=trim(str_replace('&nbsp;','',strip_tags(\App\Encryption::checkEncrypted($this->content)?\App\Encryption::decrypt($this->content):$this->content)));
	    
	    if(mb_strlen($string)==0)
	    	return '<span style="color:#999">(글자가 없는 게시글)</span>';
	    
	    return mb_substr($string,0,$length).(mb_strlen($string)>$length?'...':'');
    }
    
    public function extravar($id){
	    $query=DB::table('gallery_cadre_extravars')->where(['extravar'=>$id,'cadre'=>$this->id])->first();
	    $extravar=DB::table('gallery_extravars')->where(['id'=>$id,'gallery'=>$this->gallery])->first();
	    if(!$query||!$query->content) return $extravar->type=='checkbox'||$extravar->type=='order'?[]:null;
	    
	    $query->content=\App\Encryption::checkEncrypted($query->content)?\App\Encryption::decrypt($query->content):$query->content;
	    
	    if($extravar->type=='checkbox'||$extravar->type=='order')
	    	return explode('|',$query->content);
	    
	    return $query->content;
    }
    
}