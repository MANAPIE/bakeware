<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class File extends Model
{
    protected $table = 'files';
    protected $guarded = [];
    public $timestamps = false;
}