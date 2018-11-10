<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Layout extends Model
{
    protected $table = 'layouts';
    protected $guarded = [];
    
    public function configs(){
	    $jsonpath=base_path().'/resources/views/layout/'.$this->path.'/config.json';
	    if(!file_exists($jsonpath))
	    	return NULL;
	    return json_decode(file_get_contents($jsonpath));
    }
    
    public function hasConfig($name){
	    $query=DB::table('layouts_configs')->where(['layout'=>$this->id,'name'=>$name])->first();
	    if($query) return true;
	    return false;
    }
    
    public function config($name){
	    $query=DB::table('layouts_configs')->where(['layout'=>$this->id,'name'=>$name])->first();
	    if(!$query) return '';
	    return $query->value;
    }
    
    public function menu(){
	    if($this->menu)
		    return \App\Menu::find($this->menu);
	    return NULL;
    }
}