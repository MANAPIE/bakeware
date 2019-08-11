<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Onepage extends Model
{
    protected $table = 'page_onepages';
    protected $guarded = [];
    
    public function url(){
	    return url(($this->domain?'//'.$this->domain:'').'/'.$this->url);
    }
    
    public function pages(){
	    $pages=[];
	    $query=DB::table('page_onepage_pages')->where(['onepage'=>$this->id,'state'=>200])->orderBy('order_show')->get();
		foreach($query as $page){
			$obj=Page::find($page->page);
			if($obj->state==200){
				$pages[]=[
					'page'=>$obj,
					'background'=>$page->background,
				];
			}
		}
		
	    return $pages;
    }
    
    public function groups(){
	    if(!$this->allowed_group) return [];
	    return explode('|',$this->allowed_group);
    }
}