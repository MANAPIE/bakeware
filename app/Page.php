<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Page extends Model
{
    protected $table = 'pages';
    protected $guarded = [];
    
    public function url(){
	    return url(($this->domain?'//'.$this->domain:'').'/'.$this->url);
    }
    
    public function renewal($name,$layout,$content){
	    $id=\App\Http\Controllers\Controller::getSequence();
	    
	    DB::table('page_versions')->insert([
		   'id'=>$id,
		   'page'=>$this->id,
		   'author'=>\Auth::user()->id,
		   'title'=>$name,
		   'layout'=>$layout,
		   'content'=>$content, 
	    ]);
	    
	    return $id;
    }
    
    public function name(){
	    $name=DB::table('page_versions')->where('id',$this->version)->first()->title;
	    return \App\Encryption::checkEncrypted($name)?\App\Encryption::decrypt($name):$name;
    }
    
    public function content(){
	    $content=DB::table('page_versions')->where('id',$this->version)->first()->content;
	    return \App\Http\Controllers\Controller::filterHTML(\App\Encryption::checkEncrypted($content)?\App\Encryption::decrypt($content):$content);
    }
    
    public function layout(){
	    return DB::table('page_versions')->where('id',$this->version)->first()->layout;
    }
    
    public function type(){
	    return $this->type=='outer'?'외부':'편집';
    }
    
    public function groups(){
	    if(!$this->allowed_group) return [];
	    return explode('|',$this->allowed_group);
    }
}