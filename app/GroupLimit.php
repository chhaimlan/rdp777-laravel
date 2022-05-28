<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class GroupLimit extends Model
{
    protected $table = 'group_limits';
    protected $guarded = [];
    const UPDATED_AT = null;
    protected $casts = [
        'limit_value' =>'json'
    ];
}
