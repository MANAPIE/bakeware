<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserSetting extends Model
{
    protected $table = 'user_settings';
    protected $guarded = [];
}