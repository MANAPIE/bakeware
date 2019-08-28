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
	    
	    return (preg_replace('/\/(create|complete|edit|delete|comment|[0-9]+)/ui','',\Request::path())==(\App\Encryption::checkEncrypted($this->url)?\App\Encryption::decrypt($this->url):$this->url));
    }
}