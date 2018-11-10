<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Menu extends Model
{
    protected $table = 'menus';
    protected $guarded = [];
    
    public function items(){
	    return MenuItem::where(['menu'=>$this->id,'parent'=>0,'state'=>200])->orderBy('order_show','asc')->get();
    }
}