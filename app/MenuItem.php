<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class MenuItem extends Model
{
    protected $table = 'menus_items';
    protected $guarded = [];
    public $timestamps = false;
    
    public function submenu(){
	    return MenuItem::where(['menu'=>$this->menu,'parent'=>$this->order_show,'state'=>200])->orderBy('order_show','asc')->get();
    }
    
    public function active(){
	    if(count($this->submenu()))
		    foreach($this->submenu() as $t)
		    	if($t->active())
		    		return true;
	    
	    return (\Request::path()==$this->url);
    }
}